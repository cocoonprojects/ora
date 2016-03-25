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
	protected $timeboxForItemIdeaVoting;
	
	public function __construct(TaskService $taskService){
		$this->taskService = $taskService;
		$this->timeboxForItemIdeaVoting = self::getDefaultIntervalForItemIdeaVoting();
	}
	
	public function create($data){
		
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}
		switch ($this->params('type')) {
			case "idea-items":
				if(!$this->isAllowed($this->identity(), NULL, 'TaskManagement.Task.close-voting-idea-items')){
					$this->response->setStatusCode(403);
					return $this->response;
				}
				$itemIdeas = $this->taskService->findItemsBefore($this->timeboxForItemIdeaVoting, TaskInterface::STATUS_IDEA);
				if(sizeof($itemIdeas) > 0){
					array_walk($itemIdeas, function($idea){
						$itemId = $idea->getId();
						$results = $this->taskService->countVotesForApproveIdeaItem($itemId);
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
			default:
				$this->response->setStatusCode(404);
		}
		return $this->response;
	}
	
	public function setTimeboxForItemIdeaVoting(\DateInterval $interval){
		$this->timeboxForItemIdeaVoting = $interval;
	}
	public function getTimeboxForItemIdeaVoting(){
		return $this->timeboxForItemIdeaVoting;
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
}
