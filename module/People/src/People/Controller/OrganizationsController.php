<?php

namespace People\Controller;

use People\Service\OrganizationService;
use Zend\Filter\FilterChain;
use Zend\Filter\StringTrim;
use Zend\Filter\StripNewlines;
use Zend\Filter\StripTags;
use Zend\View\Model\JsonModel;
use ZFX\Rest\Controller\HATEOASRestfulController;

class OrganizationsController extends HATEOASRestfulController
{
	protected static $collectionOptions = ['GET', 'POST'];
	protected static $resourceOptions = [];
	
	/**
	 * 
	 * @var OrganizationService
	 */
	private $orgService;
	
	public function __construct(OrganizationService $orgService) {
		$this->orgService = $orgService;
	}

	public function getList()
	{
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}

		$organizations = $this->orgService->findOrganizations();
		return new JsonModel([
				'count' => count($organizations),
				'total' => count($organizations),
				'_embedded' => [
						'ora:organization' => array_map([$this, 'serializeOne'], $organizations)
				],
				'_links' => [
					'self' => [
							'href' => $this->url()->fromRoute('organizations'),
					]
				]
		]);
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
		
		$name = isset($data['name']) ? $filters->filter($data['name']) : null;
		$organization = $this->orgService->createOrganization($name, $this->identity());
		$url = $this->url()->fromRoute('organizations', ['id' => $organization->getId()]);
		$this->response->getHeaders()->addHeaderLine('Location', $url);
		$this->response->setStatusCode(201);
		return new JsonModel($this->serializeOne($organization));
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

	protected function serializeOne($organization) {
		return [
				'id' => $organization->getId(),
				'name' => $organization->getName(),
				'createdAt' => date_format($organization->getCreatedAt(), 'c'),
				'_links' => [
						'self' => [
								'href' => $this->url()->fromRoute('organizations', ['id' => $organization->getId()])
						],
						'ora:member' => [
								'href' => $this->url()->fromRoute('members', ['orgId' => $organization->getId()])
						]
				]
		];
	}
}