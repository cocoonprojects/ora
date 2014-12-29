<?php

namespace TaskManagement\Controller;

use ZendExtension\Mvc\Controller\AbstractHATEOASRestfulController;
use Ora\TaskManagement\TaskService;
use Ora\StreamManagement\StreamService;
use Ora\User\User;
use Ora\StreamManagement\Stream;
use Ora\IllegalStateException;
use Zend\Authentication\AuthenticationServiceInterface;
use TaskManagement\View\TaskJsonModel;

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
		
	public function __construct(TaskService $taskService, StreamService $streamService)
	{
		$this->taskService = $taskService;
		$this->streamService = $streamService;
	}
	
    public function get($id)
    {        
    	$task = $this->taskService->findTask($id);
        if(is_null($task)) {
        	$this->response->setStatusCode(404);
        	return response;
        }
    	$this->response->setStatusCode(200);
        $view = new TaskJsonModel($this->url());
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
    	$availableTasks = array();
    	
    	$streamID = $this->getRequest()->getQuery('streamID');
    	
    	if(!is_null($streamID)) 
    	{
    		/**
    		 * TODO: E' inutile la chiamata a findStream
    		 */
    		$stream = $this->streamService->findStream($streamID);
    		$availableTasks = $this->taskService->findStreamTasks($stream);
    	}
		else
		{
			$availableTasks = $this->taskService->findTasks();
		}    	

    	$this->response->setStatusCode(200);
       	$view = new TaskJsonModel($this->url());       	
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
    	$loggedUser = $this->identity()['user'];
    	
    	if(is_null($loggedUser))
    	{
    		// HTTP STATUS CODE 403: Forbidden (As a member organization)
    		$this->response->setStatusCode(403);
    		return $this->response;
    	}
    	       
    	if (!isset($data['streamID']) || !isset($data['subject']))
        {            
            // HTTP STATUS CODE 400: Bad Request
            $this->response->setStatusCode(400);
            return $this->response;
        }   
		
        $stream = $this->streamService->getStream($data['streamID']);
                     
        if(is_null($stream)) {
        	// Stream Not Found
        	$this->response->setStatusCode(404);
        	return $this->response;
        }
                
        $subject = trim($data['subject']);

	    // Definition of used Zend Validators
	    $validator_NotEmpty = new \Zend\Validator\NotEmpty();
	        
	    // Check if subject is empty
	    if (!$validator_NotEmpty->isValid($subject))
	    {
	    	// HTTP STATUS CODE 406: Not Acceptable
	        $this->response->setStatusCode(406);
	        return $this->response;
	    }
	        
	    $createdBy = $loggedUser;
	    $task = $this->taskService->createTask($stream, $subject, $createdBy);
	    // Task Created
	     
	    $this->response->setStatusCode(201);
	    $url = $this->url()->fromRoute('tasks', array('id' => $task->getId()->toString()));
	    $this->response->getHeaders()->addHeaderLine('Location', $url);
	    
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
        $task = $this->taskService->getTask($id);
        if(is_null($task)) {
            $this->response->setStatusCode(404);
            return $this->response;            	
		}
    	
    	if (!isset($data['subject']))
      	{
      	    $this->response->setStatusCode(204);	// HTTP STATUS CODE 204: No Content (Nothing to update)
      	    return $this->response;
      	}
      	
      	// TODO: Utilizzare ZendForm!
      	$data['subject'] = trim($data['subject']);
      	    
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
        $task->setSubject($data['subject'], $updatedBy);
	    $this->taskService->editTask($task);
      	
      	// HTTP STATUS CODE 202: Element Accepted
      	$this->response->setStatusCode(202);
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
    	
    	try {
			$deletedBy = $this->identity()['user'];
	      	$this->taskService->deleteTask($task, $deletedBy);
	      	// HTTP STATUS CODE 200: Completed
	      	$this->response->setStatusCode(200);
		} catch (IllegalStateException $e) {
            // HTTP STATUS CODE 406: Not Acceptable
            $this->response->setStatusCode(406);			
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
