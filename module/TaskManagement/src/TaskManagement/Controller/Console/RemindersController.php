<?php

namespace TaskManagement\Controller\Console;

use Zend\Mvc\Controller\AbstractConsoleController;
use TaskManagement\Service\TaskService;
use TaskManagement\TaskInterface;
use AcMailer\Service\MailService;


class RemindersController extends AbstractConsoleController {
	
	protected $taskService;
	protected $host;
	/**
	 * @var \DateInterval
	 */
	public function __construct(TaskService $taskService, MailService $mailService)
	{
		$this->taskService = $taskService;
		$this->mailService = $mailService;
		$this->intervalForVotingRemind = new \DateInterval('P6D');
		$this->intervalForVotingTimebox = new \DateInterval('P7D');
	}

	public function setIntervalForVotingRemind($intervalForVotingRemind)
	{
		$this->intervalForVotingRemind = intervalForVotingRemind;
	}

	public function setIntervalForVotingTimebox($intervalForVotingTimebox)
	{
		$this->intervalForVotingTimebox = $intervalForVotingTimebox;
	}

	public function setHost($host) {
		$this->host = $host;
		return $this;
	}
	

	/**
	 * @param array $data
	 * @return \Zend\Stdlib\ResponseInterface
	 */
	public function sendAction($data=null)
	{
		$tasksToNotify = $this->taskService->findIdeasCreatedBetween($this->intervalForVotingRemind, $this->intervalForVotingTimebox);

		$rv = [];
		foreach ($tasksToNotify as $task) { 

			$taskMembersWithNoApproval = $task->findMembersWithNoApproval(); // @TODO  task method to be tested

			foreach ($taskMembersWithNoApproval as $tm){

				$member = $tm->getUser();
				$message = $this->mailService->getMessage();
				$message->setTo($member->getEmail());
				$message->setSubject('Vote for approval for "'.$task->getSubject().'" item');
				
				$this->mailService->setTemplate( 'mail/reminder-add-approval.phtml', [
					'task' => $task,
					'recipient'=> $member,
					'host' => $this->host
				]);
				
				$this->mailService->send();
				$rv[$task->getId()] = $member->getEmail();
			}
		}
		var_dump($rv);
		return $rv;	
	}
	
}