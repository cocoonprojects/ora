<?php

namespace TaskManagement\Controller;

use ZendExtension\Mvc\Controller\AbstractHATEOASRestfulController;
use Zend\Authentication\AuthenticationService;
use Ora\TaskManagement\TaskService;
use Ora\ProjectManagement\ProjectService;
use Ora\User\User;
use Ora\ProjectManagement\Project;
use Ora\IllegalStateException;
use Zend\Authentication\AuthenticationServiceInterface;
use TaskManagement\View\TaskJsonModel;

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
     * @var ProjectService
     */
	private $projectService;
	
	protected $task = null;
	
	public function __construct(TaskService $taskService, AuthenticationServiceInterface $authService)
	{
		$this->taskService = $taskService;
		$this->authService = $authService;
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
     * @link http://oraproject/task-management/tasks
     * @return TaskJsonModel
     * @author Giannotti Fabio
     */
    public function getList()
    {
        // TODO: Verificare che l'utente abbia il permesso per accedere all'elenco dei task disponibili?
    	$availableTasks = $this->taskService->listAvailableTasks();
       	$this->response->setStatusCode(200);
       	$view = new TaskJsonModel();       	
        $view->setVariable('resource', $availableTasks);
        $view->setVariable('url', $this->url()->fromRoute('tasks'));
        $view->setVariable('user', $this->authService->getIdentity()['user']);
        return $view;
    }
    
    /**
     * Create a new task into specified project
     * @method POST
     * @link http://oraproject/task-management/tasks
     * @param array $data['projectID'] Parent project ID of the new task
     * @param array $data['subject'] Task subject
     * @return HTTPStatusCode
     * @author Giannotti Fabio
     */
    public function create($data)
    {
	    $response = $this->getResponse();        
    	if (!isset($data['projectID']) || !isset($data['subject']))
        {            
            // HTTP STATUS CODE 400: Bad Request
            $response->setStatusCode(400);
            return $response;
        }
        
		$project = $this->getProjectService()->getProject($data['projectID']);
		if(is_null($project)) {
        	// Project Not Found
        	$response->setStatusCode(404);
			return $response;			
		}
        // TODO: Verificare che l'utente abbia il permesso per accedere a tale progetto?
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
	        
	    $createdBy = $this->authService->getIdentity()['user'];
	    $task = $this->taskService->createTask($project, $subject, $createdBy);
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
    
    protected function getProjectService()
    {
        if (!isset($this->projectService))
        {
            $serviceLocator = $this->getServiceLocator();
            $this->projectService = $serviceLocator->get('TaskManagement\ProjectService');
        }
    
        return $this->projectService;
    }
    
}
