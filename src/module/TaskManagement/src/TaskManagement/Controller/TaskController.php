<?php

namespace TaskManagement\Controller;

use ZendExtension\Mvc\Controller\AbstractHATEOASRestfulController;
use Zend\View\Model\JsonModel;

class TaskController extends AbstractHATEOASRestfulController
{
    protected static $collectionOptions = array ('GET','POST');
    protected static $resourceOptions = array ('DELETE','GET');
	protected $taskService;

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
     * @param array $data['taskDescription'] Task description
     * @return \Zend\View\Model\JsonModel
     * @author Giannotti Fabio
     */
    public function create($data)
    {
        // Check requested parameters
    	if (isset($data['projectID']) && isset($data['taskDescription']))
    	{
        	$projectID = $data['projectID'];
    	    $taskDescription = $data['taskDescription'];
        	
    	    // TODO: recuperare il project entity in base al project ID e passarlo alla funzione di creazione nuovo task
    	    $parentProject = "PARENT_PROJECT_INVENTATO_TODO";
           	$data = $this->getTaskService()->createNewTask($parentProject, $taskDescription);
            
            // HTTP STATUS CODE 201: Created
        	$this->response->setStatusCode(201);
    	}
    	else
    	{
    	    // HTTP STATUS CODE 400: Bad Request
        	$this->response->setStatusCode(400);
            
        	$data = array("error"=>"Bad Request: parameters needed (description, projectID)");
    	}
    	
        return new JsonModel($data);
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