<?php

namespace TasksManagement\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

class TasksController extends AbstractRestfulController
{   
	private $projectService;
		
    public function getProjectService(){

    	$projectServices = '';
    	
    	if($this->projectService == null){
    		$projectServices = $this->getServiceLocator()->get('TasksManagement\Services\ProjectService');
    	}else{
    		$projectServices = $this->projectService;
    	}
    	    	
    	return $projectServices;
    }
    
	
    public function get($id)
    {
        $response = array();
        $this->response->setStatusCode(200);

        return new JsonModel($response);
    }
    
    public function getList()
    {
       	$response = array("1"=>"CIAO TASK");
       	
       	$this->response->setStatusCode(200);
        return new JsonModel($response);
    }
    
    public function create($data)
    {	    	    	
    	
    	//controlli: esistenza del progetto + esistenza di subject
    	
    	//se il progetto esiste lo passo direttamente al metodo addTask
    	
    	$projectId = $this->params('projectId');
    	$subject = $data['subject'];
    	
       	$ps = $this->getProjectService();       	
       	$response = $ps->addTask($projectId, $subject);
       	       	
       	$this->response->setStatusCode(202);       	
        return new JsonModel($response);
    }
    
    public function update($id, $data)
    {   	
      	$response = array();

        return new JsonModel($response);
    }
    
    public function delete($id)
    {
        $response = array();

        return new JsonModel($response);
    }
    
	public function options()
    {
        $this->response->setStatusCode(405);

        return array(
            'content' => 'Method Not Allowed'
        );
    }
}
