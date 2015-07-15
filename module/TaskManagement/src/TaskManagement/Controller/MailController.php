<?php

namespace TaskManagement\Controller;

use ZFX\Rest\Controller\HATEOASRestfulController;
use TaskManagement\Service\NotifyMailListener;
use TaskManagement\Service\TaskService;
use TaskManagement\Entity\Task;
use TaskManagement;
use TaskManagement\Entity\TaskMember;
use Zend\View\Model\ViewModel;


class MailController extends HATEOASRestfulController {
	
	protected static $collectionOptions = array('POST');
	protected static $resourceOptions = array('POST');
	
	protected $notifyMailListener;

	protected $taskService;
	
	public function __construct(NotifyMailListener $notifyMailListener, TaskService $taskService) {
		
		$this->notifyMailListener = $notifyMailListener;
		$this->taskService = $taskService;
		
	}
	
	public function invoke($id, $data)
	{
		$task = $this->taskService->findTask($id);
		if(!(isset($this->identity()['user']) && $this->isAllowed($this->identity()['user'], $task, 'TaskManagement.Task.sendReminder'))){
			$this->response->setStatusCode(403);
			return $this->response;
		}
		
		if (!isset($data['type']) || $data['type'] == ''){
			$this->response->setStatusCode(400);
			return $this->response;
		}
		
		$type = $data ["type"];
		switch ($type){
			case 'add-estimation':
				
				$task = $this->taskService->findTask($id);
				
				$this->notifyMailListener->reminderAddEstimation($task);
				
				break;
			default:
				$this->response->setStatusCode(405);
				break;
		}
		
		return $this->response;
		
	}
	
	protected function getCollectionOptions()
	{
		return self::$collectionOptions;
	}
	
	protected function getResourceOptions()
	{
		return self::$resourceOptions;
	}
	
}