<?php

namespace TaskManagement\Controller\Console;

use Zend\Mvc\Controller\AbstractConsoleController;
use TaskManagement\Service\TaskService;
use People\Service\OrganizationService;
use TaskManagement\TaskInterface;
use AcMailer\Service\MailService;


class RemindersController extends AbstractConsoleController {

	protected $taskService;
	protected $organizationService;
	protected $host;

	public function __construct(
		TaskService $taskService,
		MailService $mailService,
		OrganizationService $organizationService)
	{
		$this->taskService = $taskService;
		$this->organizationService = $organizationService;
		$this->mailService = $mailService;
	}

	public function setHost($host) {
		$this->host = $host;
		return $this;
	}

	/**
	 * @param array $data
	 * @return \Zend\Stdlib\ResponseInterface
	 */
	public function sendAction()
	{
		$orgs = $this->organizationService->findOrganizations();

		foreach($orgs as $org) {
			$orgId = $org->getId();
			$intervalForVotingRemind = $org->getParams()
				->get('item_idea_voting_remind_interval');

			$intervalForVotingTimebox = $org->getParams()
				->get('item_idea_voting_timebox');

			$this->write("loading org {$org->getName()} ({$org->getId()})");
			$this->write("voting remind interval is {$intervalForVotingRemind->format('%d')}");
			$this->write("voting timebox is {$intervalForVotingTimebox->format('%d')}");

			$tasksToNotify = $this->taskService
				->findIdeasCreatedBetween(
					$intervalForVotingTimebox,
					$intervalForVotingRemind,
					$org->getId()
			);

			$totTasks = count($tasksToNotify);

			$this->write("found $totTasks tasks");
			$rv = [];

			foreach ($tasksToNotify as $task) {

				$taskMembersWithNoApproval = $task->findMembersWithNoApproval();

				$totTasksMembers = count($taskMembersWithNoApproval);

				$this->write("task {$task->getId()} has $totTasksMembers to be notified");

				foreach ($taskMembersWithNoApproval as $tm){

					$member = $tm->getUser();
					$message = $this->mailService->getMessage();
					$message->setTo($member->getEmail());
					$message->setSubject('Vote for approval for "'.$task->getSubject().'" item');

					$this->mailService->setTemplate('mail/reminder-add-approval.phtml', [
						'task' => $task,
						'recipient'=> $member,
						'host' => $this->host
					]);

					$this->mailService->send();

					$this->write("{$member->getEmail()} notified (task {$task->getId()})");

					$rv[$task->getId()] = $member->getEmail();
				}
			}

			$this->write("");

		}
	}

	private function write($msg)
	{
		$now = (new \DateTime('now'))->format('Y-m-d H:s');

		echo "[$now] ", $msg, "\n";
	}

}