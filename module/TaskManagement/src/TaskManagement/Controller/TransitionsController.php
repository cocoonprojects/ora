<?php
namespace TaskManagement\Controller;

use Application\IllegalStateException;
use Application\InvalidArgumentException;
use Application\View\ErrorJsonModel;
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

	private $validator;

	public function __construct(TaskService $taskService) {
		$this->taskService = $taskService;
		$this->intervalForCloseTasks = new \DateInterval('P7D');
		$this->validator = new InArray(
			['haystack' => array('complete', 'accept', 'execute', 'close')]
		);
	}
	
	public function invoke($id, $data)
	{
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}

		if (!isset ($data['action']) || !$this->validator->isValid($data['action'])) {
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
					return $this->response;
				}
				$this->transaction()->begin();
				try {
					$task->complete($this->identity());
					$this->transaction()->commit();
					$this->response->setStatusCode ( 200 );
					$view = new TaskJsonModel($this);
					$view->setVariable('resource', $task);
				} catch ( IllegalStateException $e ) {
					$this->transaction()->rollback();
					$this->response->setStatusCode ( 412 ); // Preconditions failed
					$view = new ErrorJsonModel();
					$view->setCode(412);
					$view->setDescription($e->getMessage());
				} catch ( InvalidArgumentException $e ) {
					$this->transaction()->rollback();
					$this->response->setStatusCode ( 403 );
					$view = new ErrorJsonModel();
					$view->setCode(403);
					$view->setDescription($e->getMessage());
				}
				break;
			case "accept":
				if($task->getStatus() == Task::STATUS_ACCEPTED) {
					$this->response->setStatusCode ( 204 );
					return $this->response;
				}
				$this->transaction()->begin();
				try {
					$task->accept($this->identity(), $this->getIntervalForCloseTasks());
					$this->transaction()->commit();
					$this->response->setStatusCode ( 200 );
					$view = new TaskJsonModel($this);
					$view->setVariable('resource', $task);
				} catch ( IllegalStateException $e ) {
					$this->transaction()->rollback();
					$this->response->setStatusCode ( 412 ); // Preconditions failed
					$view = new ErrorJsonModel();
					$view->setCode(412);
					$view->setDescription($e->getMessage());
				} catch ( InvalidArgumentException $e ) {
					$this->transaction()->rollback();
					$this->response->setStatusCode ( 403 );
					$view = new ErrorJsonModel();
					$view->setCode(403);
					$view->setDescription($e->getMessage());
				}
				break;
			case "execute":
				if($task->getStatus() == Task::STATUS_ONGOING) {
					$this->response->setStatusCode ( 204 );
					return $this->response;
				}
				$this->transaction()->begin();
				try {
					$task->execute($this->identity());
					$this->transaction()->commit();
					$this->response->setStatusCode ( 200 );
					$view = new TaskJsonModel($this);
					$view->setVariable('resource', $task);
				} catch ( IllegalStateException $e ) {
					$this->transaction()->rollback();
					$this->response->setStatusCode ( 412 ); // Preconditions failed
					$view = new ErrorJsonModel();
					$view->setCode(412);
					$view->setDescription($e->getMessage());
				} catch ( InvalidArgumentException $e ) {
					$this->transaction()->rollback();
					$this->response->setStatusCode ( 403 );
					$view = new ErrorJsonModel();
					$view->setCode(403);
					$view->setDescription($e->getMessage());
				}
				break;
			case "close":
				if(!$this->isAllowed($this->identity(), $task, 'TaskManagement.Task.close')) {
					$this->response->setStatusCode ( 403 );
					return $this->response;
				};
				$this->transaction()->begin();
				try {
					$task->close($this->identity());
					$this->transaction()->commit();
					$this->response->setStatusCode ( 200 );
					$view = new TaskJsonModel($this);
					$view->setVariable('resource', $task);
				}catch ( IllegalStateException $e ) {
					$this->transaction()->rollback();
					$this->response->setStatusCode ( 412 ); // Preconditions failed
					$view = new ErrorJsonModel();
					$view->setCode(412);
					$view->setDescription($e->getMessage());
				}
				break;
			default :
				$this->response->setStatusCode ( 400 );
				$view = new ErrorJsonModel();
				$view->setCode(400);
				$view->setDescription('Unknown action value: '.$action);
		}
		
		return $view;
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
