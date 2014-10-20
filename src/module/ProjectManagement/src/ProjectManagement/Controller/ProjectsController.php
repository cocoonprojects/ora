<?php

namespace ProjectManagement\Controller;

use ZendExtension\Mvc\Controller\AbstractHATEOASRestfulController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class ProjectsController extends AbstractHATEOASRestfulController
{
    protected static $collectionOptions = array ('GET','POST');
    protected static $resourceOptions = array ('DELETE','GET');
	protected $projectService;
	
    public function get($id)
    {        
        $response = array();
        
        $this->response->setStatusCode(200);
        
        return new JsonModel($response);
    }
	
    public function getList()
    {
        $response = array();
        
        $this->response->setStatusCode(200);

        return new JsonModel($response);
    }
    
    public function create($data)
    {        
        $response = array();
        
        $this->response->setStatusCode(200);

        return new JsonModel($response);
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
}