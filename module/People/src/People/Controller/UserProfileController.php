<?php

namespace People\Controller;

use Application\Entity\User;
use People\Service\OrganizationService;
use Application\Controller\OrganizationAwareController;
use Application\Service\UserService;
use People\View\UserProfileJsonModel;

class UserProfileController extends OrganizationAwareController
{
	protected static $collectionOptions = array('GET');
	protected static $resourceOptions = array('DELETE', 'POST', 'GET', 'PUT');
	
	private $orgService;
	private $userService;
	
	public function __construct(OrganizationService $orgService, UserService $userService) {
		$this->orgService = $orgService;
		$this->userService = $userService;
	}
	
	public function get($id)
	{
		// HTTP STATUS CODE 405: Method not allowed
		$this->response->setStatusCode(409);
			
		return $this->response;	
	}
	
	public function getList()
	{
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}
		$userId = $this->params()->fromQuery('userId');
		$user = $this->userService->findUser($userId);
		
		//$orgId = $this->params()->fromQuery('orgId');
		//$organization = $this->orgService->findOrganization($orgId);
		
		$membership = $this->orgService->findUserOrganizationMemberships($user);
		foreach ($membership as $m){
			if($m->getOrganization()->getId()===$this->organization->getId()){
				$role = $m->getRole();
			}
		}
		
		$view = new UserProfileJsonModel();
		$view->setVariable('org-resource', $this->organization);
		$view->setVariable('user-resource', $user);
		$view->setVariable('role-resource', $role);
		
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
	
	public function getUserService()
	{
		return $this->userService;
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