<?php
namespace TaskManagement\Service;

use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\Event;
use Prooph\EventStore\EventStore;
use Ora\Accounting\AccountService;
use Ora\StreamManagement\StreamService;
use Application\Service\OrganizationService;
use Ora\User\User;
use Ora\TaskManagement\TaskService;
use Ora\TaskManagement\Task;

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
	/**
	 * 
	 * @var EventStore
	 */
	private $transactionManager;

	protected $listeners = array();
	
	public function __construct(TaskService $taskService, StreamService $streamService, OrganizationService $organizationService, AccountService $accountService, EventStore $transactionManager) {
		$this->taskService = $taskService;
		$this->streamService = $streamService;
		$this->organizationService = $organizationService;
		$this->accountService = $accountService;
		$this->transactionManager = $transactionManager;
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
		$this->events = $events;
	}
	
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }
}