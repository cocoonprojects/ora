<?php

namespace User\Controller;

use ZendExtension\Mvc\Controller\AbstractHATEOASRestfulController;
use Ora\Organization\OrganizationService;
use Ora\Organization\Organization;
use Zend\Filter\FilterChain;
use Zend\Filter\StringTrim;
use Zend\Filter\StripNewlines;
use Zend\Filter\StripTags;

class OrganizationsController extends AbstractHATEOASRestfulController
{
    protected static $collectionOptions = array('GET', 'POST');
    protected static $resourceOptions = array('DELETE', 'POST', 'GET', 'PUT');
    
    /**
     * 
     * @var OrganizationService
     */
    private $orgService;
    
    public function __construct(OrganizationService $orgService) {
    	$this->orgService = $orgService;
    }

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
    	$identity = $this->identity();
    	if(is_null($identity)) {
    		$this->response->setStatusCode(401);
    		return $this->response;
    	}
    	$identity = $identity['user'];
    	
    	$filters = new FilterChain();
    	$filters->attach(new StringTrim())
    			->attach(new StripNewlines())
    			->attach(new StripTags());
    	
    	$name = isset($data['name']) ? $filters->filter($data['name']) : null;
    	$organization = $this->orgService->createOrganization($name, $identity);
	    $url = $this->url()->fromRoute('organizations', array('id' => $organization->getId()->toString()));
	    $this->response->getHeaders()->addHeaderLine('Location', $url);
    	$this->response->setStatusCode(201);
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
    
    public function getOrganizationService()
    {
        return $this->orgService;
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