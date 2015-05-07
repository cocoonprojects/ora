<?php
namespace TaskManagement\Controller;

use ZFX\Rest\Controller\HATEOASRestfulController;
use Application\IllegalStateException;
use Application\InvalidArgumentException;
use TaskManagement\Service\TaskService;
use TaskManagement\Task;
use Zend\Validator\InArray;
use Zend\Permissions\Acl\Acl;

class TransitionsController extends HATEOASRestfulController
{
	protected static $resourceOptions = array ('POST');
	protected static $collectionOptions= array ();
	
	/**
	 * @var TaskService
	 */
	private $taskService;
	/**
	 * @var Application\Entity\User
	 */
	private $systemUser;
	/**
	 *@var \DateInterval
	 */
	protected $intervalForCloseTasks;
	/**
	 *
	 * @var Acl
	 */
	private $acl;
	
	public function __construct(TaskService $taskService, Acl $acl) {
		$this->taskService = $taskService;
		$this->acl = $acl;
	}
	
	public function invoke($id, $data) {
		$validator = new InArray(
			['haystack' => array('complete', 'accept','execute', 'close')]
		);
		if (!isset ($data['action']) || !$validator->isValid($data['action'])) {
			$this->response->setStatusCode ( 400 );
			return $this->response;
		}
		
		if($data["action"] == "close"){
			if(!$this->acl->isAllowed(NULL, NULL, 'Application.Host.allowLocalhost')){			
				
				$this->response->setStatusCode(404);
				return $this->response;
			}	
		}else{					
			
			$task = $this->taskService->getTask($id);
			if (is_null($task)) {
				$this->response->setStatusCode ( 404 );
				return $this->response;
			}	
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
					$task->accept($this->identity()['user']);
					$this->transaction()->commit();
					$this->response->setStatusCode ( 200 );
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
					$task->execute($this->identity()['user']);
					$this->transaction()->commit();
					$this->response->setStatusCode ( 200 );
				} catch ( IllegalStateException $e ) {
					$this->transaction()->rollback();
					$this->response->setStatusCode ( 412 ); // Preconditions failed
				} catch ( InvalidArgumentException $e ) {
					$this->transaction()->rollback();
					$this->response->setStatusCode ( 403 );
				}
				break;
			case "close":
				//recupero tutti i task accettati per i quali è stato superato il limite per assegnare gli share
				$tasksFound = $this->taskService->findAcceptedTasksBefore($this->getIntervalForCloseTasks());
				
				foreach ($tasksFound as $taskFound){
					
					$taskToClose = $this->taskService->getTask($taskFound->getId());
					
					$this->transaction()->begin();
					try {
						//TODO: controllo degli accessi con un'asserzione per verificare che sia solo l'utente SYSTEM a chiudere il task
						$taskToClose->close($this->getSystemUser());		
						$this->transaction()->commit();
					} catch ( IllegalStateException $e ) {
						$this->transaction()->rollback();
						continue; //skip task
					} catch ( InvalidArgumentException $e ) {
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
	
	public function setSystemUser($user){
		$this->systemUser = $user;
	}
	
	public function getSystemUser(){
		return $this->systemUser;
	}
	
	public function setIntervalForCloseTasks($interval){
		$this->intervalForCloseTasks = $interval;
	}
	
	public function getIntervalForCloseTasks(){
		return $this->intervalForCloseTasks;
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
