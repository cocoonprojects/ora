<?php
namespace TaskManagement\Service;

use Accounting\Service\AccountService;
use Application\Entity\User;
use Application\Service\UserService;
use People\Service\OrganizationService;
use TaskManagement\Task;
use TaskManagement\CreditsAssigned;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\Event;
use Zend\Mvc\Application;

class TransferCreditsListener implements ListenerAggregateInterface
{
	/**
	 * @var TaskService
	 */
	private $taskService;
	/**
	 * @var OrganizationService
	 */
	private $organizationService;
	/**
	 * @var AccountService
	 */
	private $accountService;
	/**
	 * @var UserService
	 */
	private $userService;

	protected $listeners = array();
	
	public function __construct(TaskService $taskService, OrganizationService $organizationService, AccountService $accountService, UserService $userService) {
		$this->taskService = $taskService;
		$this->organizationService = $organizationService;
		$this->accountService = $accountService;
		$this->userService = $userService;
	}
	
	public function attach(EventManagerInterface $events) {
		$this->listeners[] = $events->getSharedManager()->attach(Application::class, CreditsAssigned::class,
			function(Event $event) {
				$streamEvent = $event->getTarget();
				$taskId = $streamEvent->metadata()['aggregate_id'];
				$task = $this->taskService->getTask($taskId);
				$byId = $event->getParam('by');
				$by = $this->userService->findUser($byId);
				$credits = $event->getParam('credits');
				$this->execute($task, $by, $credits);
			});
	}
	
	public function detach(EventManagerInterface $events)
	{
		if($events->getSharedManager()->detach(Application::class, $this->listeners[0])) {
			unset($this->listeners[0]);
		}
	}

	public function execute(Task $task, User $by, $credits) {
		$organization = $this->organizationService->getOrganization($task->getOrganizationId());
		$payer = $this->accountService->getAccount($organization->getAccountId());
		foreach ($credits as $memberId => $amount) {
			if($amount > 0) {
				$account = $this->accountService->findPersonalAccount($memberId, $organization);
				$payee = $this->accountService->getAccount($account->getId());
				$this->accountService->transfer($payer, $payee, $amount, 'Item "' . $task->getSubject() . '" share', $by);
			}
		}
	}
}
