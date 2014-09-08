<?php

namespace TasksManagement\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

class ProjectsController extends AbstractRestfulController
{    
    
    public function get($id)
    {
        $response = array();
        $this->response->setStatusCode(200);

        return new JsonModel($response);
    }
    
    public function getList()
    {
       	$response = array("1"=>"CIAO PROGETTO");
		
       	$this->response->setStatusCode(200);
        return new JsonModel($response);
    }
    
    public function create($data)
    {	
       	$response = array();

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
