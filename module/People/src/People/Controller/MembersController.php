<?php

namespace People\Controller;

use People\Service\OrganizationService;
use ZFX\Rest\Controller\HATEOASRestfulController;
use People\View\OrganizationMembershipJsonModel;

class MembersController extends HATEOASRestfulController
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
		$identity = $this->identity();
		if(is_null($identity)) {
			$this->response->setStatusCode(401);
			return $this->response;
		}
		$identity = $identity['user'];
		
		$orgId = $this->params('orgId');
		if(is_null($orgId)) {
			$this->response->setStatusCode(400);
			return $this->response;
		}
		$organization = $this->orgService->findOrganization($orgId);
		if(is_null($organization)) {
			$this->response->setStatusCode(404);
			return $this->response;
		}
		if(!$this->isAllowed($identity, $organization, 'People.Organization.userList')) {
			$this->response->setStatusCode(403);
			return $this->response;
		}
		$memberships = $this->orgService->findOrganizationMemberships($organization);

		$view = new OrganizationMembershipJsonModel($this->url(), $identity);
		$view->setVariable('organization', $organization);
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