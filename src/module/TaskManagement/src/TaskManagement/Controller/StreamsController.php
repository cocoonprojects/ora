<?php

namespace StreamManagement\Controller;

use ZendExtension\Mvc\Controller\AbstractHATEOASRestfulController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class StreamsController extends AbstractHATEOASRestfulController
{
    protected static $collectionOptions = array ('GET','POST');
    protected static $resourceOptions = array ('DELETE','GET');
	protected $streamService;
	
    public function get($id)
    {
        // HTTP STATUS CODE 405: Method not allowed
        $this->response->setStatusCode(405);
         
        return $this->response;
    }
    
    public function getList()
    {
        // HTTP STATUS CODE 405: Method not allowed
        $this->response->setStatusCode(405);
         
        return $this->response;
    }
    
    public function create($data)
    {
        // HTTP STATUS CODE 405: Method not allowed
        $this->response->setStatusCode(405);
         
        return $this->response;
    }
    
    public function update($id, $data)
    {
        // HTTP STATUS CODE 405: Method not allowed
        $this->response->setStatusCode(405);
         
        return $this->response;
    }
    
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
    
    public function delete($id)
    {
        // HTTP STATUS CODE 405: Method not allowed
        $this->response->setStatusCode(405);
         
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
    
    protected function getStreamService() 
    {
        if (!isset($this->streamService)) 
        {
            $serviceLocator = $this->getServiceLocator();
            $this->streamService = $serviceLocator->get('TaskManagement\StreamService');
        }
        
        return $this->streamService;
    }
}