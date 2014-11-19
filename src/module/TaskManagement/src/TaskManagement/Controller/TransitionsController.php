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
	
	public function __construct(KanbanizeService $kanbanizeService) {
		$this->kanbanizeService = $kanbanizeService;
	}
	
// 	/**
// 	 *
// 	 * @param
// 	 *        	$data
// 	 */
// 	public function create($data) {
// 		// TODO inserire subject e project in $data
// 		// kanbanize api take
// 		$validator_NotEmpty = new \Zend\Validator\NotEmpty ();
// 		$validator_Digits = new \Zend\Validator\Digits ();
		
// 		if (! isset ( $data ["boardid"] )) {
// 			// bad request
// 			$this->response->setStatusCode ( 400 );
// 			return $this->response;
// 		}
		
// 		$boardId = $data ["boardid"];
// 		if (! $validator_NotEmpty->isValid ( $boardId ) || ! $validator_Digits->isValid ( $boardId )) {
// 			// request not correct
// 			$this->response->setStatusCode ( 406 );
// 			return $this->response;
// 		}
		
// 		// TODO create task based on $data received
// 		$taskId = uniqid ();
// 		try {
// 			$result = $this->kanbanizeService->createNewTask ( 1, "arharharharha", $boardId );
// 		} catch ( OperationFailedException $e ) {
// 			$this->response->setStatusCode ( 400 );
// 			return $this->response;
// 		}
// 		catch (KanbanizeApiException $e2){
// 			$this->response->setStatusCode ( 504 );
// 			return $this->response;
// 		}
		
// 		$this->response->setStatusCode ( 201 );
// 		return $this->response;
// 	}
	
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
			
					$result = $this->kanbanizeService->moveToCompleted ( $task );
			
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
						
						$result = $this->kanbanizeService->acceptTask ( $task );
				
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
				$this->kanbanizeService->moveBackToOngoing ( $task );
				
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
