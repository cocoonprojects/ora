<?php
namespace TaskManagement\Controller;

use ZendExtension\Mvc\Controller\AbstractHATEOASRestfulController;
use Zend\Authentication\AuthenticationServiceInterface;
use Ora\IllegalStateException;
use Ora\InvalidArgumentException;
use Ora\TaskManagement\TaskService;
use Ora\TaskManagement\Task;
use Ora\StreamManagement\StreamService;
use TaskManagement\View\TaskJsonModel;
use BjyAuthorize\Service\Authorize;

class TasksController extends AbstractHATEOASRestfulController
{
    protected static $collectionOptions = ['GET', 'POST'];
    protected static $resourceOptions = ['DELETE', 'GET', 'PUT'];
    
	/**
	 * 
	 * @var TaskService
	 */
    private $taskService;
    /**
     * 
     * @var StreamService
     */
	private $streamService;
	
	public function __construct(TaskService $taskService, StreamService $streamService, Authorize $authorize)
	{
		$this->taskService = $taskService;
		$this->streamService = $streamService;		
		$this->authorize = $authorize;
	}
	
    public function get($id)
    {        
    	$task = $this->taskService->findTask($id);
        if(is_null($task)) {
        	$this->response->setStatusCode(404);
        	return $this->response;
        }
    	$this->response->setStatusCode(200);
        $view = new TaskJsonModel($this->url(), $this->identity()['user'], $this->authorize);
        $view->setVariable('resource', $task);
        return $view;
    }
	
    /**
     * Return a list of available tasks
     * @method GET
     * @link http://oraproject/task-management/tasks?streamID=[uuid]
     * @return TaskJsonModel
     * @author Giannotti Fabio
     */
    public function getList()
    {    	
    	$streamID = $this->getRequest()->getQuery('streamID');
		$availableTasks = is_null($streamID) ? $this->taskService->findTasks() : $this->taskService->findStreamTasks($streamID);

    	$this->response->setStatusCode(200);
       	$view = new TaskJsonModel($this->url(), $this->identity()['user'], $this->authorize);          	

        $view->setVariable('resource', $availableTasks);
        return $view;
    }
    
    /**
     * Create a new task into specified stream
     * @method POST
     * @link http://oraproject/task-management/tasks
     * @param array $data['streamID'] Parent stream ID of the new task
     * @param array $data['subject'] Task subject
     * @return HTTPStatusCode
     * @author Giannotti Fabio
     */
    public function create($data)
    {   
    	
    	if (!isset($data['streamID']) || !isset($data['subject']))
        {            
            // HTTP STATUS CODE 400: Bad Request
            $this->response->setStatusCode(400);
            return $this->response;
        }   
		
    	$identity = $this->identity();    	
    	if(is_null($identity))
    	{
    		$this->response->setStatusCode(401);
    		return $this->response;
    	}
    	$identity = $this->identity()['user'];
    	       
        $stream = $this->streamService->getStream($data['streamID']);
        if(is_null($stream)) {
        	// Stream Not Found
        	$this->response->setStatusCode(404);
        	return $this->response;
        }
                
	    // Definition of used Zend Validators
	    $validator_NotEmpty = new \Zend\Validator\NotEmpty();
	        
	    // Check if subject is empty
	    if (!$validator_NotEmpty->isValid($data['subject']))
	    {
	    	// HTTP STATUS CODE 406: Not Acceptable
	        $this->response->setStatusCode(406);
	        return $this->response;
	    }
	        
	    $task = $this->taskService->createTask($stream, $data['subject'], $identity);
	    $url = $this->url()->fromRoute('tasks', array('id' => $task->getId()->toString()));
	    $this->response->getHeaders()->addHeaderLine('Location', $url);
	    $this->response->setStatusCode(201);
	    
    	return $this->response;
    }

    /**
     * Update existing task with new data
     * @method PUT
     * @link http://oraproject/task-management/tasks/[:ID]
     * @param array $id ID of the Task to update 
     * @param array $data['subject'] Update Subject for the selected Task
     * @return HTTPStatusCode
     * @author Giannotti Fabio
     */
    public function update($id, $data)
    {
    	if (!isset($data['subject'])) {
      	    $this->response->setStatusCode(204);	// HTTP STATUS CODE 204: No Content (Nothing to update)
      	    return $this->response;
      	}
      	
        $task = $this->taskService->getTask($id);
        if(is_null($task)) {
            $this->response->setStatusCode(404);
            return $this->response;            	
		}
    	
      	// Definition of used Zend Validators
      	$validator_NotEmpty = new \Zend\Validator\NotEmpty();
      	    
      	// ...if exist check if subject it's empty
        if (!$validator_NotEmpty->isValid($data['subject']))
        {
            // HTTP STATUS CODE 406: Not Acceptable
            $this->response->setStatusCode(406);
            return $this->response;
        }
          	
	    $updatedBy = $this->identity()['user'];
	    $this->transaction()->begin();
	    try {
        	$task->setSubject($data['subject'], $updatedBy);
        	$this->transaction()->commit();
	      	// HTTP STATUS CODE 202: Element Accepted
	      	$this->response->setStatusCode(202);
	    } catch (\Exception $e) {
	    	$this->transaction()->rollback();
	    }
      	
        return $this->response;
    }
    
	/**
     * Delete single existing task with specified ID
     * @method DELETE
     * @link http://oraproject/task-management/tasks/[:ID]
     * @return HTTPStatusCode
     * @author Giannotti Fabio
     */
    public function delete($id)
    {     	
        $task = $this->taskService->getTask($id);
        if(is_null($task)) {
            $this->response->setStatusCode(404);
            return $this->response;            	
		}
    	if($task->getStatus() == Task::STATUS_DELETED) {
			$this->response->setStatusCode(204);
			return $this->response;
		}
    	
		$deletedBy = $this->identity()['user'];
		$this->transaction()->begin();
    	try {
    		$task->delete($deletedBy);
    		$this->transaction()->commit();
	      	$this->response->setStatusCode(200);
		} catch (IllegalStateException $e) {
			$this->transaction()->rollback();
            $this->response->setStatusCode(412);	// Preconditions failed			
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
