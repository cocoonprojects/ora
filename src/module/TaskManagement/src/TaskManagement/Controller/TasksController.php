<?php

namespace TaskManagement\Controller;

use ZendExtension\Mvc\Controller\AbstractHATEOASRestfulController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class TasksController extends AbstractHATEOASRestfulController
{
    protected static $collectionOptions = array ('GET','POST');
    protected static $resourceOptions = array ('DELETE','GET');
	protected $taskService;
	protected $projectService;
	
	// INIZIALIZZAZIONE
	// - Inizializzo/valorizzo $project con l'entità reale
    
	// GET - singolo perchè con un parametro di ID
    public function get($id)
    {        
        $response = array();
        
        $this->response->setStatusCode(200);

        return new JsonModel($response);
    }
	
    /**
     * Return a list of available tasks
     * @method GET
     * @link http://oraproject/task-management/task
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
     * @link http://oraproject/task-management/task
     * @param array $data['projectID'] Parent project ID of the new task
     * @param array $data['subject'] Task subject
     * @return HTTPStatusCode
     * @author Giannotti Fabio
     */
    public function create($data)
    {        
        // Definition of used Zend Validators
        $validator_NotEmpty = new \Zend\Validator\NotEmpty();
        //$validator_StringLength = new \Zend\Validator\StringLength();
        //$validator_Integer = new \Zend\I18n\Validator\Int();
        
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
        
        $projectID = trim($data['projectID']);        
        $subject = trim($data['subject']);
        
        // TODO: Verificare che l'utente abbia il permesso per accedere a tale progetto?
        
        // Check if subject is empty
        if (!$validator_NotEmpty->isValid($subject))
        {
            // HTTP STATUS CODE 406: Not Acceptable
            $this->response->setStatusCode(406);
        
            return $this->response;
        }
        
        // Check if projectID value it's empty
        if (!$validator_NotEmpty->isValid($projectID))
        {
            // HTTP STATUS CODE 406: Not Acceptable
            $this->response->setStatusCode(406);
            
            return $this->response;
        }
        
        // Check if exist project with specific Project ID 
        $project = $this->getProjectService()->findProjectByID($projectID);
        if (!($project instanceof \Ora\ProjectManagement\Project))
        {
            // HTTP STATUS CODE 404: Not Acceptable
            $this->response->setStatusCode(404);
            
            return $this->response;
        }
        
        // Creo il nuovo task
       	$this->getTaskService()->createNewTask($project, $subject);
        
        // HTTP STATUS CODE 201: Created
    	$this->response->setStatusCode(201);
    	
    	return $this->response;
    }
    
        
    
    
    /*
    // PUT
    public function update($id, $data)
    {   	
      	$response = array();

        return new JsonModel($response);
    }
    
    // DELETE - singolo perchè definiamo un ID
    public function delete($id)
    {
        $response = array();

        return new JsonModel($response);
    }
    */
    
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