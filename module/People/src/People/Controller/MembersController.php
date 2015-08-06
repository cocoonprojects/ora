<?php

namespace People\Controller;

use Application\DomainEntityUnavailableException;
use Application\DuplicatedDomainEntityException;
use People\View\OrganizationMembershipJsonModel;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\EventManagerInterface;
use Application\Controller\OrganizationAwareController;
use People\Service\OrganizationService;

class MembersController extends OrganizationAwareController
{
	protected static $collectionOptions = array('GET', 'DELETE', 'POST');
	protected static $resourceOptions = array('DELETE', 'POST');
	
	public function __construct(OrganizationService $organizationService){
		parent::__construct($organizationService);
	}

	public function getList()
	{
		$identity = $this->identity();
		if(is_null($identity)) {
			$this->response->setStatusCode(401);
			return $this->response;
		}
		$identity = $identity['user'];
		
		if(!$this->isAllowed($identity, $this->organization, 'People.Organization.userList')) {
			$this->response->setStatusCode(403);
			return $this->response;
		}
		$memberships = $this->getOrganizationService()->findOrganizationMemberships($this->organization);

		$view = new OrganizationMembershipJsonModel($this->url(), $identity);
		$view->setVariable('organization', $this->organization);
		$view->setVariable('resource', $memberships);
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

		$organization = $this->getOrganizationService()->getOrganization($this->params('orgId'));
		if(is_null($organization)) {
			$this->response->setStatusCode(404);
			return $this->response;
		}
		
		$this->transaction()->begin();
		try {
			$organization->addMember($identity);
			$this->transaction()->commit();
			$this->response->setStatusCode(201);
		} catch (DuplicatedDomainEntityException $e) {
			$this->transaction()->rollback();
			$this->response->setStatusCode(204);
		}
		return $this->response;
	}

	public function deleteList()
	{
		$identity = $this->identity();
		if(is_null($identity)) {
			$this->response->setStatusCode(401);
			return $this->response;
		}
		$identity = $identity['user'];

		$organization = $this->getOrganizationService()->getOrganization($this->params('orgId'));
		if(is_null($organization)) {
			$this->response->setStatusCode(404);
			return $this->response;
		}
		
		$this->transaction()->begin();
		try {
			$organization->removeMember($identity);
			$this->transaction()->commit();
			$this->response->setStatusCode(200);
		} catch (DomainEntityUnavailableException $e) {
			$this->transaction()->rollback();
			$this->response->setStatusCode(204);
		}
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
}