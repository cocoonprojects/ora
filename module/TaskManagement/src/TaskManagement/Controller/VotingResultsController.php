<?php

namespace TaskManagement\Controller;

use ZFX\Rest\Controller\HATEOASRestfulController;
use TaskManagement\Service\TaskService;
use TaskManagement\TaskInterface;

class VotingResultsController extends HATEOASRestfulController {
	
	protected static $collectionOptions = ['POST'];
	protected static $resourceOptions = [];
	
	/**
	 * @var TaskService
	 */
	protected $taskService;
	/**
	 * @var \DateInterval
	 */
	protected $timeboxForVoting = [];
	
	public function __construct(TaskService $taskService){
		$this->taskService = $taskService;
		$this->timeboxForVoting[TaskInterface::STATUS_IDEA] = self::getDefaultIntervalForItemIdeaVoting();
		$this->timeboxForVoting[TaskInterface::STATUS_COMPLETED] = self::getDefaultIntervalForCompletedIdeaVoting();
	}
	
	public function create($data){
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}

		$type = $this->params('type');
		if (empty($type) && !empty($data['type'])) {
			$type = $data['type'];
		}

		switch ($type) {
			case "idea-items":
				if(!$this->isAllowed($this->identity(), NULL, 'TaskManagement.Task.close-voting-idea-items')){
					$this->response->setStatusCode(403);
					return $this->response;
				}
				$itemIdeas = $this->taskService->findItemsBefore($this->timeboxForVoting[TaskInterface::STATUS_IDEA], TaskInterface::STATUS_IDEA);
				if(sizeof($itemIdeas) > 0){
					array_walk($itemIdeas, function($idea){
						$itemId = $idea->getId();
						$results = $this->taskService->countVotesForApproveItem(TaskInterface::STATUS_IDEA, $itemId);
						$item = $this->taskService->getTask($itemId);
						$this->transaction()->begin();
						try {
							if($results['votesFor'] > $results['votesAgainst']){
								$item->open($this->identity());
							}else{
								$item->archive($this->identity());
							}
							$this->transaction()->commit();
						}catch (\Exception $e) {
							$this->transaction()->rollback();
							$this->response->setStatusCode(500);
							return $this->response;
						}
					});
				}
				$this->response->setStatusCode(200);
				break;
			case "completed-items":
				if(!$this->isAllowed($this->identity(), NULL, 'TaskManagement.Task.close-voting-completed-items')){
					$this->response->setStatusCode(403);
					return $this->response;
				}
				$itemsCompleted = $this->taskService->findItemsBefore($this->timeboxForVoting[TaskInterface::STATUS_COMPLETED], TaskInterface::STATUS_COMPLETED);
				$operationResult = [];
				if(sizeof($itemsCompleted) > 0){
					array_walk($itemsCompleted, function($completed) use (&$operationResult) {
						$itemId = $completed->getId();
						$results = $this->taskService->countVotesForItem(TaskInterface::STATUS_COMPLETED, $itemId);
						$item = $this->taskService->getTask($itemId);
						$this->transaction()->begin();
						try {
							if($results['votesFor'] > $results['votesAgainst']){
								$item->accept($this->identity());
								$operationResult[$itemId] = 'closed';
							}else{
								$item->execute($this->identity());
								$operationResult[$itemId] = 'reopened';
							}
							$this->transaction()->commit();
						}catch (\Exception $e) {
							$this->transaction()->rollback();
							$this->response->setStatusCode(500);
							return $this->response;
						}
					});
				}
				// $this->response->setContent(json_encode($operationResult));
				$this->response->setStatusCode(200);
				break;
			default:
				$this->response->setStatusCode(404);
		}
		return $this->response;
	}
	
	public function setTimeboxForVoting($voteType, \DateInterval $interval){
		$this->timeboxForVoting[$voteType] = $interval;
	}
	public function getTimeboxForVoting($voteType){
		return $this->timeboxForVoting[$voteType];
	}
	public function getTaskService(){
		return $this->taskService;
	}
	protected function getCollectionOptions(){
		return self::$collectionOptions;
	}
	
	protected function getResourceOptions(){
		return self::$resourceOptions;
	}
	protected static function getDefaultIntervalForItemIdeaVoting(){
		return new \DateInterval('P7D');
	}
	protected static function getDefaultIntervalForCompletedIdeaVoting(){
		return new \DateInterval('P7D');
	}
}
