<?php

namespace TaskManagement\Controller;

use ZFX\Rest\Controller\HATEOASRestfulController;
use TaskManagement\Service\TaskService;
use FlowManagement\Service\FlowService;
use Zend\View\Model\JsonModel;

class HistoryController extends HATEOASRestfulController {

	protected static $collectionOptions = ['GET'];
	protected static $resourceOptions = ['GET'];

	/**
	 * @var TaskService
	 */
	protected $taskService;
	
	/**
	 * @var FlowService
	 */
	protected $flowService;
	
	public function __construct(TaskService $taskService){
		$this->taskService = $taskService;
	}
	
	public function get($id) {
		$streamEvents = $this->taskService->getTaskHistory($id);

		return new JsonModel($streamEvents);
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
