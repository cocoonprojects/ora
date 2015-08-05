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
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
        }

		if(!$this->isAllowed($this->identity(), $organization, 'People.Organization.userList')) {
			$this->response->setStatusCode(403);
			return $this->response;
		}
		$memberships = $this->getOrganizationService()->findOrganizationMemberships($this->organization);

		$view = new OrganizationMembershipJsonModel($this->url(), $this->identity());
		$view->setVariable('organization', $this->organization);
		$view->setVariable('resource', $memberships);
		return $view;
	}
	
	public function create($data)
	{
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}

		$organization = $this->getOrganizationService()->getOrganization($this->params('orgId'));
		if(is_null($organization)) {
			$this->response->setStatusCode(404);
			return $this->response;
		}
		
		$this->transaction()->begin();
		try {
			$organization->addMember($this->identity());
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
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}

		$organization = $this->getOrganizationService()->getOrganization($this->params('orgId'));
		if(is_null($organization)) {
			$this->response->setStatusCode(404);
			return $this->response;
		}
		
		$this->transaction()->begin();
		try {
			$organization->removeMember($this->identity());
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
