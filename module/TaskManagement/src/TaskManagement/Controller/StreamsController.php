<?php

namespace TaskManagement\Controller;

use Zend\Filter\FilterChain;
use Zend\Filter\StringTrim;
use Zend\Filter\StripNewlines;
use Zend\Filter\StripTags;
use ZFX\Rest\Controller\HATEOASRestfulController;
use People\Service\OrganizationService;
use TaskManagement\View\StreamJsonModel;
use TaskManagement\Service\StreamService;

class StreamsController extends HATEOASRestfulController
{
	protected static $collectionOptions = array ('GET','POST');
	protected static $resourceOptions = array ('DELETE','GET');
	/**
	 * 
	 * @var StreamService
	 */
	protected $streamService;
	/**
	 * 
	 * @var OrganizationService
	 */
	protected $organizationService;
	
	public function __construct(StreamService $streamService, OrganizationService $organizationService) {
		$this->streamService = $streamService;
		$this->organizationService = $organizationService;
	}
	
	public function get($id)
	{
		// HTTP STATUS CODE 405: Method not allowed
		$this->response->setStatusCode(405);
		 
		return $this->response;
	}
	
	public function getList()
	{
		$identity = $this->identity();
		if(is_null($identity)) {
			$this->response->setStatusCode(401);
			return $this->response;
	   	}
	   	$identity = $identity['user'];

	   	$orgId = $this->getRequest()->getQuery('orgId');
	   	
	   	if (is_null($orgId)){
	   		$this->response->setStatusCode(400);
	   		return $this->response;
	   	}
	   	
	   	$organization = $this->organizationService->findOrganization($orgId);
	   	if (is_null($organization)){
	   		$this->response->setStatusCode(404);
	   		return $this->response;
	   	}
	   	
	   	if(!$this->isAllowed($identity, $organization, 'TaskManagement.Stream.list')){
	   		$this->response->setStatusCode(403);
	   		return $this->response;
	   	}
	   	
	   	$streams = $this->streamService->findStreams($organization);
	   	$view = new StreamJsonModel($this->url(), $identity);
	   	$view->setVariable('resource', $streams);
		return $view;
	}
	
	public function create($data)
	{
		$identity = $this->identity();
		if(is_null($identity)) {
			$this->response->setStatusCode(401);
			return $this->response;
		}
		$identity = $identity['user'];
		
		if(!isset($data['organizationId'])) {
			$this->response->setStatusCode(400);
			return $this->response;
		}
		$organization = $this->organizationService->getOrganization($data['organizationId']);
		if(is_null($organization)) {
			$this->response->setStatusCode(404);
			return $this->response;
		}
		$filters = new FilterChain();
		$filters->attach(new StringTrim())
				->attach(new StripNewlines())
				->attach(new StripTags());
		
		$subject = isset($data['subject']) ? $filters->filter($data['subject']) : null;
		$stream = $this->streamService->createStream($organization, $subject, $identity);
		$url = $this->url()->fromRoute('streams', array('id' => $stream->getId()));
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
	
	public function getStreamService() 
	{
		return $this->streamService;
	}
	
	public function getOrganizationService()
	{
		return $this->organizationService;
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