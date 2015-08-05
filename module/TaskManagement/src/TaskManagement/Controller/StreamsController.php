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
use Zend\Mvc\MvcEvent;
use Zend\EventManager\EventManagerInterface;
use People\Entity\Organization;

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
	/**
	 *
	 * @var Organization
	 */
	private $organization;
	
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

	   	if(!$this->isAllowed($identity, $this->organization, 'TaskManagement.Stream.list')){
	   		$this->response->setStatusCode(403);
	   		return $this->response;
	   	}
	   	
	   	$streams = $this->streamService->findStreams($this->organization);
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
		
		$filters = new FilterChain();
		$filters->attach(new StringTrim())
				->attach(new StripNewlines())
				->attach(new StripTags());
		
		$subject = isset($data['subject']) ? $filters->filter($data['subject']) : null;
		$organization = $this->organizationService->getOrganization($this->params('orgId'));
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
	public function setEventManager(EventManagerInterface $events)
	{
		parent::setEventManager($events);
	
		// Register a listener at high priority
		$events->attach('dispatch', array($this, 'findOrganization'), 50);
		}
	
	public function findOrganization(MvcEvent $e){
		
		$orgId = $this->params('orgId');
		$response = $this->getResponse();
		
		if (is_null($orgId)){
			$response->setStatusCode(400);
			return $response;
		}
		
		$this->organization = $this->getOrganizationService()->findOrganization($orgId);
		if (is_null($this->organization)){
			$response->setStatusCode(404);
			return $response;
		}
	
		return;
	}
	
}