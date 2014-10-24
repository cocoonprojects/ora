<?php

namespace TaskManagement\Controller;

use ZendExtension\Mvc\Controller\AbstractHATEOASRestfulController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class TasksController extends AbstractHATEOASRestfulController
{
    protected static $collectionOptions = array('GET', 'POST');
    protected static $resourceOptions = array('DELETE', 'POST', 'GET', 'PUT');
    
	protected $taskService;
	protected $projectService;
	
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
     * @return \Zend\View\Model\JsonModel
     * @author Giannotti Fabio
     */
    public function getList()
    {
        // TODO: Verificare che l'utente abbia il permesso per accedere all'elenco dei task disponibili?
        $availableTasks = $this->getTaskService()->listAvailableTasks();
       	
       	$this->response->setStatusCode(200);
       	
        return new JsonModel($availableTasks);
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
        // Definition of used Zend Validators
        $validator_NotEmpty = new \Zend\Validator\NotEmpty();
        
        if (!isset($data['projectID']))
        {            
            // HTTP STATUS CODE 400: Bad Request
            $this->response->setStatusCode(400);
            
            return $this->response;
        }
        
        if (!isset($data['subject']))
        {           
            // HTTP STATUS CODE 400: Bad Request
            $this->response->setStatusCode(400);
            
            return $this->response;
        }
        
        $data['projectID'] = trim($data['projectID']);        
        $data['subject'] = trim($data['subject']);
        
        // TODO: Verificare che l'utente abbia il permesso per accedere a tale progetto?
        
        // Check if subject is empty
        if (!$validator_NotEmpty->isValid($data['subject']))
        {
            // HTTP STATUS CODE 406: Not Acceptable
            $this->response->setStatusCode(406);
        
            return $this->response;
        }
        
        // Check if projectID value it's empty
        if (!$validator_NotEmpty->isValid($data['projectID']))
        {
            // HTTP STATUS CODE 406: Not Acceptable
            $this->response->setStatusCode(406);
            
            return $this->response;
        }
        
        // Check if project with specific ProjectID exist 
        $project = $this->getProjectService()->findProject($data['projectID']);
        if (!($project instanceof \Ora\ProjectManagement\Project))
        {
            // HTTP STATUS CODE 404: Not Found
            $this->response->setStatusCode(404);
            
            return $this->response;
        }
        
        // Creation of new task
       	$this->getTaskService()->createNewTask($project, $data['subject']);
        
        // HTTP STATUS CODE 201: Created
    	$this->response->setStatusCode(201);
    	
    	return $this->response;
    }

    /**
     * Update existing task with new data
     * @method PUT
     * @link http://oraproject/task-management/tasks/[:ID]
     * @param array $id ID of the Task to update 
     * @param array $data['subject'] Updated Subject for the selected Task
     * @return HTTPStatusCode
     * @author Giannotti Fabio
     */
    public function update($id, $data)
    {        
        // Check if any field must be updated
      	if (sizeof($data) == 0)
      	{
      	    // HTTP STATUS CODE 204: No Content (Nothing to update)
      	    $this->response->setStatusCode(204);
      	
      	    return $this->response;
      	}
      	     	 
      	// Check if subject exist...
      	if (isset($data['subject']))
      	{
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
          	
          	$this->task->setSubject($data['subject']);
      	}
      	      	      	
      	// Edit existing task
      	$this->getTaskService()->editTask($this->task);
      	
      	// HTTP STATUS CODE 202: Element Accepted
      	$this->response->setStatusCode(202);
      	
        return $this->response;
    }
    
    /**
     * Update all existing tasks
     * @method PUT
     * @link http://oraproject/task-management/tasks
     * @return HTTPStatusCode
     * @author Giannotti Fabio
     */
    public function replaceList($data)
    {
        // HTTP STATUS CODE 405: Method not allowed
        $this->response->setStatusCode(405);
         
        return $this->response;
    }    
    
    public function deleteList()
    {
        // HTTP STATUS CODE 405: Method not allowed
        $this->response->setStatusCode(405);
         
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
      	// Check if task with specified ID exist
      	$task = $this->getTaskService()->findTask($id);
      	if (!($task instanceof \Ora\TaskManagement\Task))
      	{
      	    // HTTP STATUS CODE 404: Not Found
      	    $this->response->setStatusCode(404);
            
      	    return $this->response;
      	}
      	
      	// TODO: Stiamo controllando se il task da cancellare si trova nello status di ONGOIN. Gli
      	// stati al momento sono salvati dentro l'entità. Fare in modo che qui non ci sia scritto
      	// direttamente 20, ma che venga utilizzata una costante globale o qualcosa di simile.
        if ($task->getStatus() !== 20) // Ongoing...
        {
            // HTTP STATUS CODE 406: Not Acceptable
            $this->response->setStatusCode(406);
            
            return $this->response;
        }
      	
      	// Delete existing task
      	$this->getTaskService()->deleteTask($task);
      	
      	// HTTP STATUS CODE 200: Completed
      	$this->response->setStatusCode(200);
      	
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
    
    protected function getJsonModelClass()
    {
        return $this->jsonModelClass;
    }
    
    protected function getProjectService()
    {
        if (!isset($this->projectService))
        {
            $serviceLocator = $this->getServiceLocator();
            $this->projectService = $serviceLocator->get('ProjectManagement\ProjectService');
        }
    
        return $this->projectService;
    }
    
    protected function getTaskService() 
    {
        if (!isset($this->taskService)) 
        {
            $serviceLocator = $this->getServiceLocator();
            $this->taskService = $serviceLocator->get('TaskManagement\TaskService');
        }
        
        return $this->taskService;
    }
}