<?php

namespace Application\Controller;

use Zend\Filter\FilterChain;
use Zend\Filter\StringTrim;
use Zend\Filter\StripNewlines;
use Zend\Filter\StripTags;
use People\Organization;
use People\Service\OrganizationService;
use ZFX\Rest\Controller\HATEOASRestfulController;
use Application\View\OrganizationMembershipJsonModel;

class MembershipsController extends HATEOASRestfulController
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
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}

		$memberships = $this->orgService->findUserOrganizationMemberships($this->identity());
		
		$view = new OrganizationMembershipJsonModel($this->url(), $this->identity());
		$view->setVariable('resource', $memberships);
		return $view;
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