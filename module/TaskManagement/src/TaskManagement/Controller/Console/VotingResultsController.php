<?php

namespace TaskManagement\Controller\Console;

use Zend\Mvc\Controller\AbstractConsoleController;
use TaskManagement\Service\TaskService;
use People\Service\OrganizationService;
use TaskManagement\TaskInterface;
use Zend\Console\Request as ConsoleRequest;
use Application\Entity\User;
use Application\Service\UserService;

class VotingResultsController extends AbstractConsoleController {

	protected $taskService;

	protected $organizationService;

	protected $userService;

	public function __construct(
		TaskService $taskService,
		OrganizationService $organizationService,
		UserService $userService
	) {
		$this->taskService = $taskService;
		$this->organizationService = $organizationService;
		$this->userService = $userService;
	}

	public function closePollsAction()
	{
		$request = $this->getRequest();

        if (!$request instanceof ConsoleRequest) {
        	$this->write("use only from a console!");

			exit(1);
        }

		$type = $this->params('type');

		if (!$type) {
			$this->write("run with 'public/index.php closepolls [idea-items|completed-items]");

			exit(1);
		}

		$systemUser = $this->userService
						   ->findUser(User::SYSTEM_USER);

		if (!$systemUser) {
			$this->write("missing system user, aborting");

			exit(1);
        }

        $this->write("loaded system user {$systemUser->getName()}");

		$orgs = $this->organizationService->findOrganizations();

		foreach($orgs as $org) {

			$this->write("org {$org->getName()} ({$org->getId()})");

			if ($type == 'idea-items') {
				$this->closePollsForIdeaItems($systemUser, $org);
			}

			if ($type == 'completed-items') {
				$this->closePollsForCompletedItems($systemUser, $org);
			}

			$this->write("");
		}
	}

	private function closePollsForIdeaItems($systemUser, $org)
	{
		$timeboxForVoting = $org->getParams()
			->get('item_idea_voting_timebox');

		$this->write("timebox for ideas is {$timeboxForVoting->format('%d')}");

		$itemIdeas = $this->taskService->findItemsBefore(
			$timeboxForVoting,
			TaskInterface::STATUS_IDEA,
			$org->getId()
		);

		$totItemIdeas = count($itemIdeas);

		$this->write("found $totItemIdeas idea items to process");

		if($totItemIdeas == 0) {
			return;
		}

		array_walk($itemIdeas, function($idea) use($systemUser){
			$itemId = $idea->getId();
			$results = $this->taskService
							->countVotesForItem(TaskInterface::STATUS_IDEA, $itemId);
			$item = $this->taskService->getTask($itemId);

			$this->transaction()->begin();

			try {
				if($results['votesFor'] > $results['votesAgainst']){
					$this->write("opening task $itemId: {$results['votesFor']} votes for, {$results['votesAgainst']} against");
					$item->open($systemUser);
				}else{
					$this->write("archiving task $itemId: {$results['votesFor']} votes for, {$results['votesAgainst']} against");
					$item->archive($systemUser);
				}
				$this->transaction()->commit();
			}catch (\Exception $e) {
				$this->transaction()->rollback();
				$this->write("error: {$e->getMessage()}");
			}
		});
	}

	private function closePollsForCompletedItems($systemUser, $org)
	{
		$timeboxForVoting = $org->getParams()
			->get('completed_item_voting_timebox');

		$this->write("timebox for completed items is {$timeboxForVoting->format('%d')}");

		$itemsCompleted = $this->taskService->findItemsBefore(
			$timeboxForVoting,
			TaskInterface::STATUS_COMPLETED,
			$org->getId()
		);

		$totItemsCompleted = count($itemsCompleted);

		$this->write("found $totItemsCompleted completed items to process");

		if($totItemsCompleted == 0) {
			return;
		}

		array_walk($itemsCompleted, function($completed) use ($systemUser) {
			$itemId = $completed->getId();
			$results = $this->taskService
							->countVotesForItem(TaskInterface::STATUS_COMPLETED, $itemId);
			$item = $this->taskService->getTask($itemId);

			$this->transaction()->begin();

			try {
				if($results['votesFor'] > $results['votesAgainst']){
					$this->write("accepting task $itemId: {$results['votesFor']} votes for, {$results['votesAgainst']} against");
					$item->accept($systemUser);
				}else{
					$this->write("reopening task $itemId: {$results['votesFor']} votes for, {$results['votesAgainst']} against");
					$item->reopen($systemUser);
				}
				$this->transaction()->commit();
			}catch (\Exception $e) {
				$this->transaction()->rollback();
				$this->write("error: {$e->getMessage()}");
			}
		});
	}

	private function write($msg)
	{
		$now = (new \DateTime('now'))->format('Y-m-d H:s');

		echo "[$now] ", $msg, "\n";
	}
}
