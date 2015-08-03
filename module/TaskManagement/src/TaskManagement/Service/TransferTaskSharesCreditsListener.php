<?php
namespace TaskManagement\Service;

use Accounting\Service\AccountService;
use Accounting\IllegalAmountException;
use Application\Entity\User;
use Application\Service\UserService;
use People\Service\OrganizationService;
use Prooph\EventStore\EventStore;
use TaskManagement\Task;
use TaskManagement\TaskClosed;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\Event;
use Zend\Mvc\Application;

class TransferTaskSharesCreditsListener implements ListenerAggregateInterface
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
	/**
	 * @var EventStore
	 */
	private $transactionManager;

	protected $listeners = array();
	
	public function __construct(TaskService $taskService, OrganizationService $organizationService, AccountService $accountService, UserService $userService, EventStore $transactionManager) {
		$this->taskService = $taskService;
		$this->organizationService = $organizationService;
		$this->accountService = $accountService;
		$this->userService = $userService;
		$this->transactionManager = $transactionManager;
	}
	
	public function attach(EventManagerInterface $events) {
		$this->listeners[] = $events->getSharedManager()->attach(Application::class, TaskClosed::class,
			function(Event $event) {
				$streamEvent = $event->getTarget();
				$taskId = $streamEvent->metadata()['aggregate_id'];
				$task = $this->taskService->getTask($taskId);
				$byId = $event->getParam('by');
				$by = $this->userService->findUser($byId);
				$this->execute($task, $by);
			});
	}
	
	public function detach(EventManagerInterface $events)
	{
		if($events->getSharedManager()->detach(Application::class, $this->listeners[0])) {
			unset($this->listeners[0]);
		}
	}

	public function execute(Task $task, User $by) {
		$organization = $this->organizationService->getOrganization($task->getOrganizationId());
		$payer = $this->accountService->getAccount($organization->getAccountId());

 		$this->transactionManager->beginTransaction();
		$credits = $task->getMembersCredits();
		try{
			foreach ($credits as $memberId => $amount) {
				if($amount > 0) {
					$account = $this->accountService->findPersonalAccount($memberId, $organization);
					$payee = $this->accountService->getAccount($account->getId());
					$payer->transferOut(-$amount, $payee, "Item '" . $task->getSubject() . "' (#" . $task->getId() .") credits share", $by);
					$payee->transferIn($amount, $payer, "Item '" . $task->getSubject() . "' (#" . $task->getId() .") credits share", $by);
				}
			}
	 		$this->transactionManager->commit();
		}catch(IllegalAmountException $e){
			$this->transactionManager->rollback();
			throw $e;
		}
	}
}