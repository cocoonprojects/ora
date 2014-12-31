<?php
namespace TaskManagement\Controller;

use ZendExtension\Mvc\Controller\AbstractHATEOASRestfulController;
use Ora\Kanbanize\KanbanizeService;
use Ora\Kanbanize\KanbanizeTask;
use Ora\Kanbanize\Exception\IllegalRemoteStateException;
use Ora\Kanbanize\Exception\KanbanizeApiException;
use Ora\Kanbanize\Exception\AlreadyInDestinationException;
use Zend\View\Model\ViewModel;
use Ora\TaskManagement\TaskService;

class TransitionsController extends AbstractHATEOASRestfulController
{
	protected static $resourceOptions = array ('POST');
	protected static $collectionOptions= array ();
	
	/**
	 *
	 * @var KanbanizeService
	 */
	protected $kanbanizeService;

	/**
	 * @var TaskService
	 */
	private $taskService;
	
	public function __construct(TaskService $taskService, KanbanizeService $kanbanizeService) {
		$this->taskService = $taskService;
		$this->kanbanizeService = $kanbanizeService;
	}
	
	public function invoke($id, $data) {
		if (! isset ( $data ['action'] )) {
			$this->response->setStatusCode ( 400 );
			return $this->response;
		}
		$task = $this->taskService->findTask($id);
		if (is_null($task)) {
			$this->response->setStatusCode ( 404 );
			return $this->response;
			
		}
		$action = $data ["action"];

		$boardId = $task->getBoardId();
		$taskId = $task->getTaskId();
		
		//$kanbanizeTask = new KanbanizeTask ( $taskId, $boardId, $id, new \DateTime (), $createdBy );
		$result = 0;
		try {
			switch ($action) {
				case "completed":
					$this->kanbanizeService->moveToCompleted ( $task );
					$this->response->setStatusCode ( 200 );
					break;
				case "accept":
					$this->kanbanizeService->acceptTask ( $task );
					$this->response->setStatusCode ( 200 );
					break;
				case "ongoing" :
					$this->kanbanizeService->moveBackToOngoing ( $task );
					$this->response->setStatusCode ( 200 );
					break;
				default :
					$this->response->setStatusCode ( 400 );
					break;
			}
		} catch ( OperationFailedException $e ) {
			$this->response->setStatusCode ( 400 );
		} catch ( AlreadyInDestinationException $e ) {
			$this->response->setStatusCode ( 204 );
		} catch ( IllegalRemoteStateException $e ) {
			$this->response->setStatusCode ( 400 );
		} catch ( KanbanizeApiException $e ) {
			$this->response->setStatusCode ( 504 );
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
