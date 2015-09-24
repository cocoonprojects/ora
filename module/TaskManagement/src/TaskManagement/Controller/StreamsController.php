<?php

namespace TaskManagement\Controller;

use Zend\Filter\FilterChain;
use Zend\Filter\StringTrim;
use Zend\Filter\StripNewlines;
use Zend\Filter\StripTags;
use TaskManagement\View\StreamJsonModel;
use TaskManagement\Service\StreamService;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\EventManagerInterface;
use Application\Controller\OrganizationAwareController;
use People\Service\OrganizationService;

class StreamsController extends OrganizationAwareController
{
	protected static $collectionOptions = array ('GET','POST');
	protected static $resourceOptions = array ('DELETE','GET');
	/**
	 * 
	 * @var StreamService
	 */
	protected $streamService;
	
	public function __construct(StreamService $streamService, OrganizationService $organizationService) {
		parent::__construct($organizationService);
		$this->streamService = $streamService;
	}
	
	public function get($id)
	{
		// HTTP STATUS CODE 405: Method not allowed
		$this->response->setStatusCode(405);
		 
		return $this->response;
	}
	
	public function getList()
	{
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}

	   	if(!$this->isAllowed($this->identity(), $this->organization, 'TaskManagement.Stream.list')){
	   		$this->response->setStatusCode(403);
	   		return $this->response;
	   	}
	   	
	   	$streams = $this->streamService->findStreams($this->organization);
	   	$view = new StreamJsonModel($this->url(), $this->identity(), $this->organization);
	   	$view->setVariable('resource', $streams);
		return $view;
	}
	
	public function create($data)
	{
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}

		$filters = new FilterChain();
		$filters->attach(new StringTrim())
				->attach(new StripNewlines())
				->attach(new StripTags());
		
		$subject = isset($data['subject']) ? $filters->filter($data['subject']) : null;
		$organization = $this->getOrganizationService()->getOrganization($this->organization->getId());
		$stream = $this->streamService->createStream($organization, $subject, $this->identity());
		$url = $this->url()->fromRoute('streams', ['orgId' => $organization->getId(),'id' => $stream->getId()]);
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
	
	protected function getCollectionOptions()
	{
		return self::$collectionOptions;
	}
	
	protected function getResourceOptions()
	{
		return self::$resourceOptions;
	}
}
