<?php
namespace TaskManagement\Service;

use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\Event;
use People\Service\OrganizationService;
use Accounting\Service\AccountService;
use Application\Entity\User;
use TaskManagement\Service\TaskService;
use TaskManagement\Task;

class TransferTaskSharesCreditsListener implements ListenerAggregateInterface {
	/**
	 * 
	 * @var TaskService
	 */
	private $taskService;
	/**
	 * 
	 * @var StreamService
	 */
	private $streamService;
	/**
	 * 
	 * @var OrganizationService
	 */
	private $organizationService;
	/**
	 * 
	 * @var AccountService
	 */
	private $accountService;

	protected $listeners = array();
	
	public function __construct(TaskService $taskService, StreamService $streamService, OrganizationService $organizationService, AccountService $accountService) {
		$this->taskService = $taskService;
		$this->streamService = $streamService;
		$this->organizationService = $organizationService;
		$this->accountService = $accountService;
	}
	
	public function execute(Task $task, User $by) {
		$credits = $task->getMembersCredits();
		$stream = $this->streamService->getStream($task->getStreamId());
		$organization = $this->organizationService->getOrganization($stream->getOrganizationId());
		$payer = $this->accountService->getAccount($organization->getAccountId());
		
// 		$this->transactionManager->beginTransaction();
		$members = $task->getMembers();
		foreach ($credits as $memberId => $amount) {
			if($amount > 0) {
				$accountId = $members[$memberId]['accountId'];
				$payee = $this->accountService->getAccount($accountId);
				$payer->transferOut(-$amount, $payee, "Item '" . $task->getSubject() . "' (#" . $task->getId() .") credits share", $by);
				$payee->transferIn($amount, $payer, "Item '" . $task->getSubject() . "' (#" . $task->getId() .") credits share", $by);
			}
		}
// 		$this->transactionManager->commit();
	}

	public function attach(EventManagerInterface $events) {
		$that = $this;
		$this->listeners[] = $events->getSharedManager()->attach('TaskManagement\TaskService', Task::EVENT_CLOSED, function(Event $event) use ($that) {
			$task = $event->getTarget();
			$by = $event->getParam('by');
			$that->execute($task, $by);
		});
	}
	
    public function detach(EventManagerInterface $events)
    {
		if($events->getSharedManager()->detach('TaskManagement\TaskService', $this->listeners[0])) {
			unset($this->listeners[0]);
		}
    }
}