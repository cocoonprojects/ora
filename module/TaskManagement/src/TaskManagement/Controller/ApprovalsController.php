<?php

namespace TaskManagement\Controller;

use ZFX\Rest\Controller\HATEOASRestfulController;
use TaskManagement\Service\TaskService;
use TaskManagement\TaskInterface;

class ApprovalsController extends HATEOASRestfulController {
	
	protected static $collectionOptions = ['GET'];
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
	
	public function getList(){
		
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}
		switch ($this->params('type')) {
			case "idea-items":
				if(!$this->isAllowed($this->identity(), NULL, 'TaskManagement.Approval.idea-items')){
					$this->response->setStatusCode(403);
					return $this->response;
				}
				$itemIdeas = $this->taskService->findItemsBefore($this->timeboxForItemIdeaVoting, TaskInterface::STATUS_IDEA);
				foreach ($itemIdeas as $idea){
					
				}
				var_dump($approvalsToClose);
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
