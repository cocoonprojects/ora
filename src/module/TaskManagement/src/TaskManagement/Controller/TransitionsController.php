<?php
namespace TaskManagement\Controller;

use ZendExtension\Mvc\Controller\AbstractHATEOASRestfulController;
use Ora\Kanbanize\KanbanizeService;
use Ora\Kanbanize\ReadModel\KanbanizeTask;
use Ora\Kanbanize\Exception\IllegalRemoteStateException;
use Ora\Kanbanize\Exception\KanbanizeApiException;
use Ora\Kanbanize\Exception\AlreadyInDestinationException;
use Zend\View\Model\ViewModel;
use Ora\TaskManagement\TaskService;
use Ora\TaskManagement\Task;
use Ora\IllegalStateException;

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
		$task = $this->taskService->getTask($id);
		if (is_null($task)) {
			$this->response->setStatusCode ( 404 );
			return $this->response;
			
		}
		$action = $data ["action"];
		switch ($action) {
			case "complete":
				if($task->getStatus() == Task::STATUS_COMPLETED) {
					$this->response->setStatusCode ( 204 );
					break;
				}
				$this->transaction()->begin();
				try {
					$task->complete($this->identity()['user']);
					$this->transaction()->commit();
					$this->response->setStatusCode ( 200 );
				} catch ( IllegalStateException $e ) {
					$this->transaction()->rollback();
					$this->response->setStatusCode ( 400 );
				}
				break;
			case "accept":
				if($task->getStatus() == Task::STATUS_ACCEPTED) {
					$this->response->setStatusCode ( 204 );
					break;
				}
				$this->transaction()->begin();
				try {
					$task->accept($this->identity()['user']);
					$this->transaction()->commit();
					$this->response->setStatusCode ( 200 );
				} catch ( IllegalStateException $e ) {
					$this->transaction()->rollback();
					$this->response->setStatusCode ( 400 );
				}
				break;
			case "execute":
				if($task->getStatus() == Task::STATUS_ONGOING) {
					$this->response->setStatusCode ( 204 );
					break;
				}
				$this->transaction()->begin();
				try {
					$task->execute($this->identity()['user']);
					$this->transaction()->commit();
					$this->response->setStatusCode ( 200 );
				} catch ( IllegalStateException $e ) {
					$this->transaction()->rollback();
					$this->response->setStatusCode ( 400 );
				}
				break;
			default :
				$this->response->setStatusCode ( 400 );
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
