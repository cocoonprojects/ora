<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/Kanbanize for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace TaskManagement\Controller;

use ZendExtension\Mvc\Controller\AbstractHATEOASRestfulController;
use Ora\Kanbanize\KanbanizeTask;
use Zend\View\Model\ViewModel;
use Kanbanize\Service\KanbanizeService;
use Zend\Db\Sql\Predicate\IsNull;
use Ora\Kanbanize\Exception\IllegalRemoteStateException;
use Ora\Kanbanize\Exception\KanbanizeApiException;
use Ora\TaskManagement\TaskService;
use Ora\Kanbanize\Exception\AlreadyInDestinationException;

class TaskTransitionsController extends AbstractHATEOASRestfulController
{
	//protected static $resourceOptions = array ('GET','POST','PUT');
	//protected static $collectionOptions = array ('DELETE','GET');
	protected static $resourceOptions = array ('POST');
	protected static $collectionOptions= array ();
	
	/**
	 *
	 * @var \TaskManagement\Service\KanbanizeService
	 */
	protected $kanbanizeService;

	/**
	 * @var TaskService
	 */
	private $taskService;
	
	/**
	 *
	 * @param
	 *        	$data
	 */
	public function create($data) {
		// TODO inserire subject e project in $data
		// kanbanize api take
		$validator_NotEmpty = new \Zend\Validator\NotEmpty ();
		$validator_Digits = new \Zend\Validator\Digits ();
		
		if (! isset ( $data ["boardid"] )) {
			// bad request
			$this->response->setStatusCode ( 400 );
			return $this->response;
		}
		
		$boardId = $data ["boardid"];
		if (! $validator_NotEmpty->isValid ( $boardId ) || ! $validator_Digits->isValid ( $boardId )) {
			// request not correct
			$this->response->setStatusCode ( 406 );
			return $this->response;
		}
		
		// TODO create task based on $data received
		$taskId = uniqid ();
		try {
			$result = $this->getKanbanizeService ()->createNewTask ( 1, "arharharharha", $boardId );
		} catch ( OperationFailedException $e ) {
			$this->response->setStatusCode ( 400 );
			return $this->response;
		}
		catch (KanbanizeApiException $e2){
			$this->response->setStatusCode ( 504 );
			return $this->response;
		}
		
		$this->response->setStatusCode ( 201 );
		return $this->response;
	}
	
	public function invoke($id, $data) {
		$validator_NotEmpty = new \Zend\Validator\NotEmpty ();
		$validator_Digits = new \Zend\Validator\Digits ();
		// actions -> accept | OnGoing
		if (! isset ( $data ['action'] )) {
			$this->response->setStatusCode ( 400 );
			return $this->response;
		}
		
		$action = $data ["action"];
		
		if (! isset ( $id )) {
			// bad request
			$this->response->setStatusCode ( 400 );
			return $this->response;
		} else if (! $validator_NotEmpty->isValid ( $id )) {
			// request not correct
			$this->response->setStatusCode ( 406 );
			return $this->response;
		}
		
		

		$task = $this->getTaskService()->findTaskById($id);
		if (!isset($task)||$task ==null ){
			// no task found 
			$this->response->setStatusCode ( 404 );
			return $this->response;
			
		}
		
		$boardId = $task->getBoardId();
		$taskId = $task->getTaskId();
		
		//$kanbanizeTask = new KanbanizeTask ( $taskId, $boardId, $id, new \DateTime (), $createdBy );
		$result = 0;
		switch ($action) {
			
			case "completed":
				try {
			
					$result = $this->getKanbanizeService ()->moveToCompleted ( $task );
			
				} catch ( OperationFailedException $e ) {
					$this->response->setStatusCode ( 400 );
					return $this->response;
				}catch ( AlreadyInDestinationException $e3 ) {
					$this->response->setStatusCode ( 204 );
					return $this->response;
				}
				catch ( IllegalRemoteStateException $e1 ) {
					$this->response->setStatusCode ( 400 );
					return $this->response;
				}
				catch (KanbanizeApiException $e2){
					$this->response->setStatusCode ( 504 );
					return $this->response;
				}
			
				$this->response->setStatusCode ( 200 );
				return $this->response;
			
				break;
			
			case "accept":
				try {
						
						$result = $this->getKanbanizeService ()->acceptTask ( $task );
				
				} catch ( OperationFailedException $e ) {
					$this->response->setStatusCode ( 400 );
					return $this->response;
				}catch ( AlreadyInDestinationException $e3 ) {
					$this->response->setStatusCode ( 204 );
					return $this->response;
				}
				 catch ( IllegalRemoteStateException $e1 ) {
					$this->response->setStatusCode ( 400 );
					return $this->response;
				}
				catch (KanbanizeApiException $e2){
					$this->response->setStatusCode ( 504 );
					return $this->response;
				}
				
				$this->response->setStatusCode ( 200 );
				return $this->response;
				
				break;
			case "ongoing" :
				try{
				$this->getKanbanizeService ()->moveBackToOngoing ( $task );
				
				}catch(OperationFailedException $e){
					$this->response->setStatusCode ( 400 );
					return $this->response;
				}catch(AlreadyInDestinationException $e2){
					$this->response->setStatusCode ( 204 );
					return $this->response;
				}
				catch(IllegalRemoteStateException $e3){
					$this->response->setStatusCode ( 400 );
					return $this->response;
				}
				catch (KanbanizeApiException $e4){
					$this->response->setStatusCode ( 504 );
					return $this->response;
				}
				
					$this->response->setStatusCode ( 200 );
					return $this->response;
				break;
			default :
				$this->response->setStatusCode ( 400 );
				break;
		}
		
		return $this->response;
	}
	
     /**
      * @return \Kanbanize\Service\KanbanizeService
      */
    protected function getKanbanizeService(){
     	if (!isset($this->kanbanizeService))
     		$this->kanbanizeService = $this->getServiceLocator ()->get ( 'TaskManagement\Service\Kanbanize' );
		return $this->kanbanizeService;
	}

	/**
	 * @return \Ora\TaskManagement\TaskService
	 */
	protected function getTaskService()
	{
		if (!isset($this->taskService))
		{
			$serviceLocator = $this->getServiceLocator();
			$this->taskService = $serviceLocator->get('TaskManagement\TaskService');
		}
	
		return $this->taskService;
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
