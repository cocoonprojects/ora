<?php

namespace TaskManagement\Controller;

use ZendExtension\Mvc\Controller\AbstractHATEOASRestfulController;
use Zend\Authentication\AuthenticationService;
use Ora\TaskManagement\TaskService;
use Ora\StreamManagement\StreamService;
use Ora\User\User;
use Ora\StreamManagement\Stream;
use Ora\IllegalStateException;
use Zend\Authentication\AuthenticationServiceInterface;
use TaskManagement\View\TaskJsonModel;
use Ora\Organization\OrganizationService;

class TasksController extends AbstractHATEOASRestfulController
{
    protected static $collectionOptions = array('GET', 'POST');
    protected static $resourceOptions = array('DELETE', 'GET', 'PUT');
    
    /**
     * 
     * @var AuthenticationService
     */
	private $authService;
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
			
	/**
	 *
	 * @var OrganizationService
	 */
	private $organizationService;
		
	protected $task = null;
	
	public function __construct(TaskService $taskService, AuthenticationServiceInterface $authService, StreamService $streamService, OrganizationService $organizationService)
	{
		$this->taskService = $taskService;
		$this->authService = $authService;
		$this->streamService = $streamService;
		$this->organizationService = $organizationService;
	}
	
	public function preDispatch($e)
	{
		$taskId = $this->params()->fromRoute('id');
        if (!empty($taskId)) 
        {
            // Check if task with specified ID exist
        	$this->task = $this->taskService->getTask($taskId);
            if(is_null($this->task)) {
                $this->response->setStatusCode(404);
                return $this->response;            	
            }
        }
	}

	    	    
    public function get($id)
    {        
        // HTTP STATUS CODE 405: Method not allowed
        $this->response->setStatusCode(405);
        return $this->response;
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
    		$stream = $this->projectService->getProject($streamID);
    		$availableTasks = $this->taskService->findStreamTasks($stream);
    	}
		else
		{
			$availableTasks = $this->taskService->findTasks();
		}    	

    	$this->response->setStatusCode(200);
       	$view = new TaskJsonModel();       	
        $view->setVariable('resource', $availableTasks);
        $view->setVariable('url', $this->url()->fromRoute('tasks'));
        $view->setVariable('user', $this->authService->getIdentity()['user']);
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
    	$response = $this->getResponse();
    	
    	$loggedUser = $this->authService->getIdentity()['user'];
    	
    	if(is_null($loggedUser))
    	{
    		// HTTP STATUS CODE 403: Forbidden (As a member organization)
    		$response->setStatusCode(403);
    		return $response;
    	}
    	       
    	if (!isset($data['streamID']) || !isset($data['subject']))
        {            
            // HTTP STATUS CODE 400: Bad Request
            $response->setStatusCode(400);
            return $response;
        }   
		
        $stream = $this->streamService->getStream($data['streamID']);
                     
        if(is_null($stream)) {
        	// Stream Not Found
        	$response->setStatusCode(404);
        	return $response;
        }
                
        $subject = trim($data['subject']);

	    // Definition of used Zend Validators
	    $validator_NotEmpty = new \Zend\Validator\NotEmpty();
	        
	    // Check if subject is empty
	    if (!$validator_NotEmpty->isValid($subject))
	    {
	    	// HTTP STATUS CODE 406: Not Acceptable
	        $response->setStatusCode(406);
	        return $response;
	    }
	        
	    $createdBy = $loggedUser;
	    $task = $this->taskService->createTask($stream, $subject, $createdBy);
	    // Task Created
	     
	    $response->setStatusCode(201);
	    $url = $this->url()->fromRoute('tasks', array('id' => $task->getId()->toString()));
	    $response->getHeaders()->addHeaderLine('Location', $url);
	    
    	return $response;
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
	   	if (!isset($data['subject']))
      	{
      	    // HTTP STATUS CODE 204: No Content (Nothing to update)
      	    $this->response->setStatusCode(204);
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
          	
	    $updatedBy = $this->authService->getIdentity()['user'];
        $this->task->setSubject($data['subject'], $updatedBy);
	    $this->taskService->editTask($this->task);
      	
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
		try {
			$deletedBy = $this->authService->getIdentity()['user'];
	      	$this->taskService->deleteTask($this->task, $deletedBy);
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
