<?php

namespace TaskManagement\Controller;

use Application\Controller\OrganizationAwareController;
use People\Service\OrganizationService;
use TaskManagement\Service\StreamService;
use Zend\Filter\FilterChain;
use Zend\Filter\StringTrim;
use Zend\Filter\StripNewlines;
use Zend\Filter\StripTags;
use Zend\View\Model\JsonModel;

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
		$hal['count'] = count($streams);
		$hal['total'] = count($streams);
		$hal['_links']['self']['href'] = $this->url()->fromRoute('collaboration', [
				'orgId'=>$this->organization->getId(),
				'controller' => 'streams']);
		$hal['_embedded']['ora:stream'] = array_column(array_map([$this, 'serializeOne'], $streams), null, 'id');
		return new JsonModel($hal);
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
		$url = $this->url()->fromRoute('collaboration', ['orgId' => $organization->getId(), 'controller' => 'streams', 'id' => $stream->getId()]);
		$this->response->getHeaders()->addHeaderLine('Location', $url);
		$this->response->setStatusCode(201);
		return new JsonModel($this->serializeOne($stream));
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

	protected function serializeOne($stream) {
		return [
			'id' => $stream->getId (),
			'subject' => $stream->getSubject (),
			'createdAt' => date_format($stream->getCreatedAt(), 'c'),
			'_links' => [
					'self' => $this->url()->fromRoute('collaboration', [
							'id' => $stream->getId(),
							'orgId' => $this->organization->getId(),
							'controller' => 'streams']),
			],
		];
	}
}
