<?php

namespace People\Controller;

use Application\DomainEntityUnavailableException;
use Application\DuplicatedDomainEntityException;
use People\Service\OrganizationService;
use ZFX\Rest\Controller\HATEOASRestfulController;
use People\View\OrganizationMembershipJsonModel;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\EventManagerInterface;
use People\Entity\Organization;

class MembersController extends HATEOASRestfulController
{
	protected static $collectionOptions = array('GET', 'DELETE', 'POST');
	protected static $resourceOptions = array('DELETE', 'POST');
	
	/**
	 * 
	 * @var OrganizationService
	 */
	private $orgService;
	/**
	 *
	 * @var Organization
	 */
	private $organization;
	
	public function __construct(OrganizationService $orgService) {
		$this->orgService = $orgService;
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
		$memberships = $this->orgService->findOrganizationMemberships($this->organization);

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

		$organization = $this->orgService->getOrganization($this->params('orgId'));
		
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

		$organization = $this->orgService->getOrganization($this->params('orgId'));
		
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