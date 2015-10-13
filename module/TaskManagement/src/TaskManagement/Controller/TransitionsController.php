<?php
namespace TaskManagement\Controller;

use Application\IllegalStateException;
use Application\InvalidArgumentException;
use TaskManagement\Service\TaskService;
use TaskManagement\Task;
use TaskManagement\View\TaskJsonModel;
use Zend\Validator\InArray;
use ZFX\Rest\Controller\HATEOASRestfulController;

class TransitionsController extends HATEOASRestfulController
{
	protected static $resourceOptions = ['POST'];
	protected static $collectionOptions= ['POST'];
	
	/**
	 * @var TaskService
	 */
	private $taskService;
	/**
	 *@var \DateInterval
	 */
	protected $intervalForCloseTasks;

	public function __construct(TaskService $taskService) {
		$this->taskService = $taskService;
		$this->intervalForCloseTasks = new \DateInterval('P7D');
	}
	
	public function invoke($id, $data)
	{
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}

		$validator = new InArray(
			['haystack' => array('start','complete', 'accept', 'execute', 'close')]
		);
		if (!isset ($data['action']) || !$validator->isValid($data['action'])) {
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
			case "start":
				if($task->getStatus()==Task::STATUS_ONGOING){
					$this->response->setStatusCode(204);
					break;
				}
				$this->transaction()->begin();
				try {
					$task->start($this->identity());
					$this->transaction()->commit();
					$this->response->setStatusCode ( 200 );
					$view = new TaskJsonModel($this);
					$view->setVariable('resource', $task);
					return $view;
				} catch ( IllegalStateException $e ) {
					$this->transaction()->rollback();
					$this->response->setStatusCode ( 412 ); // Preconditions failed
				} catch ( InvalidArgumentException $e ) {
					$this->transaction()->rollback();
					$this->response->setStatusCode ( 403 );
				}			
				break;
			case "complete":
				if($task->getStatus() == Task::STATUS_COMPLETED) {
					$this->response->setStatusCode ( 204 );
					break;
				}
				$this->transaction()->begin();
				try {
					$task->complete($this->identity());
					$this->transaction()->commit();
					$this->response->setStatusCode ( 200 );
					$view = new TaskJsonModel($this);
					$view->setVariable('resource', $task);
					return $view;
				} catch ( IllegalStateException $e ) {
					$this->transaction()->rollback();
					$this->response->setStatusCode ( 412 ); // Preconditions failed
				} catch ( InvalidArgumentException $e ) {
					$this->transaction()->rollback();
					$this->response->setStatusCode ( 403 );
				}
				break;
			case "accept":
				if($task->getStatus() == Task::STATUS_ACCEPTED) {
					$this->response->setStatusCode ( 204 );
					break;
				}
				$this->transaction()->begin();
				try {
					$task->accept($this->identity(), $this->getIntervalForCloseTasks());
					$this->transaction()->commit();
					$this->response->setStatusCode ( 200 );
					$view = new TaskJsonModel($this);
					$view->setVariable('resource', $task);
					return $view;
				} catch ( IllegalStateException $e ) {
					$this->transaction()->rollback();
					$this->response->setStatusCode ( 412 ); // Preconditions failed
				} catch ( InvalidArgumentException $e ) {
					$this->transaction()->rollback();
					$this->response->setStatusCode ( 403 );
				}
				break;
			case "execute":
				if($task->getStatus() == Task::STATUS_ONGOING) {
					$this->response->setStatusCode ( 204 );
					break;
				}
				$this->transaction()->begin();
				try {
					$task->execute($this->identity());
					$this->transaction()->commit();
					$this->response->setStatusCode ( 200 );
					$view = new TaskJsonModel($this);
					$view->setVariable('resource', $task);
					return $view;
				} catch ( IllegalStateException $e ) {
					$this->transaction()->rollback();
					$this->response->setStatusCode ( 412 ); // Preconditions failed
				} catch ( InvalidArgumentException $e ) {
					$this->transaction()->rollback();
					$this->response->setStatusCode ( 403 );
				}
				break;
			default :
				$this->response->setStatusCode ( 400 );
				break;
		}
		
		return $this->response;
	}
	
	public function create($data)
	{
		if(is_null($this->identity())){
			$this->response->setStatusCode(401);
			return $this->response;
		}

		switch ($data["action"])
		{
			case "close":
				if(!$this->isAllowed($this->identity(), NULL, 'TaskManagement.Task.closeTasksCollection')) {
					$this->response->setStatusCode(403);
					return $this->response;
				}
				
				//recupero tutti i task accettati per i quali è stato superato il limite per assegnare gli share
				$tasksFound = $this->taskService->findAcceptedTasksBefore($this->getIntervalForCloseTasks());
				
				foreach ($tasksFound as $taskFound){
					$taskToClose = $this->taskService->getTask($taskFound->getId());
					$this->transaction()->begin();
					try {
						$taskToClose->close($this->identity());
						$this->transaction()->commit();
					} catch ( IllegalStateException $e ) {
						$this->transaction()->rollback();
						continue; //skip task
					}
				}
				$this->response->setStatusCode ( 200 );
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
	
	public function setIntervalForCloseTasks($interval){
		$this->intervalForCloseTasks = $interval;
	}
	
	public function getIntervalForCloseTasks(){
		return $this->intervalForCloseTasks;
	}
	
	public function getTaskService(){
		return $this->taskService;
	}
}
