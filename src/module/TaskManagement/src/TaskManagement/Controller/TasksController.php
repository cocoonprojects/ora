<?php

namespace TaskManagement\Controller;

use ZendExtension\Mvc\Controller\AbstractHATEOASRestfulController;
use Zend\View\Model\JsonModel;

class TasksController extends AbstractHATEOASRestfulController
{
    protected static $collectionOptions = array ('GET','POST');
    protected static $resourceOptions = array ('DELETE','GET');
	protected $taskService;
	
	// INIZIALIZZAZIONE
	   // - Inizializzo/valorizzo $project con l'entità reale
	
	// GET - singolo perchè con un parametro di ID
    /* public function get($id)
    {
        $response = array();
        
        $this->response->setStatusCode(200);

        return new JsonModel($response);
    }
    
    // GET - prende tutto perchè senza parametri
    public function getList()
    {
       	$response = array("1"=>"CIAO TASK");
       	
       	$this->response->setStatusCode(200);
       	
        return new JsonModel($response);
    }*/
    
    /**
     * @method POST
     * @link http://oraproject/task-management/task
     * @param array $data['projectID'] Parent project ID of the new task
     * @param array $data['subject'] Task subject
     * @return \Zend\View\Model\JsonModel
     * @author Giannotti Fabio
     */
    public function create($data)
    {        
        // Definition of used Zend Validators
        $validator_NotEmpty = new \Zend\Validator\NotEmpty();
        $validator_StringLength = new \Zend\Validator\StringLength();
        
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
        // TODO: Verificare che esista realmente un progetto con ID specificato
        // TODO: Verificare che l'utente abbia il permesso per accedere a tale progetto?
        if (!$validator_NotEmpty->isValid($projectID))
        {
            // HTTP STATUS CODE 406: Not Acceptable
            $this->response->setStatusCode(406);
            
            return $this->response;
        }
        
        $subject = trim($data['subject']);
        // TODO: inserire validazione sui parametri usando zend_validator
        // - subject: lunghezza (al momento non definita)
        if (!$validator_NotEmpty->isValid($subject))
        {
            // HTTP STATUS CODE 406: Not Acceptable
            $this->response->setStatusCode(406);
        
            return $this->response;
        }

        // Check the max length of task subject (specified into task entity)
        $validator_StringLength->setMin(20);
        $validator_StringLength->setMax(2000);
        if (!$validator_StringLength($subject))
        {
            // HTTP STATUS CODE 406: Not Acceptable
            $this->response->setStatusCode(406);
            
            return $this->response;
        }
        
	    // TODO: recuperare il project entity dalla variabile definita nel 
	    // controller e controllare che sia un'entità valida
	    //$project = $this->projectService->findProject($projectID);
	    $project = "PARENT_PROJECT_INVENTATO_TODO";
       	$this->getTaskService()->createNewTask($projectID, $subject);
        
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