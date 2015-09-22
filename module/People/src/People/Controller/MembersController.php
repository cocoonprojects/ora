<?php

namespace People\Controller;

use Application\DomainEntityUnavailableException;
use Application\DuplicatedDomainEntityException;
use People\View\OrganizationMembershipJsonModel;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\EventManagerInterface;
use Application\Controller\OrganizationAwareController;
use People\Service\OrganizationService;
use Zend\Validator\NotEmpty;
use Zend\I18n\Validator\Int;
use Zend\Validator\ValidatorChain;
use Zend\Validator\GreaterThan;

class MembersController extends OrganizationAwareController
{
	protected static $collectionOptions = array('GET', 'DELETE', 'POST');
	protected static $resourceOptions = array('DELETE', 'POST');
	
	/**
	 *
	 * @var integer
	 */
	protected $pageSize;
	
	public function __construct(OrganizationService $organizationService){
		parent::__construct($organizationService);
		$this->pageSize = 20;
	}

	public function getList()
	{
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
        }

		if(!$this->isAllowed($this->identity(), $this->organization, 'People.Organization.userList')) {
			$this->response->setStatusCode(403);
			return $this->response;
		}
		$validator = new ValidatorChain();
		$validator->attach(new NotEmpty())
			->attach(new Int())
			->attach(new GreaterThan(['min' => 0, 'inclusive' => false]));
		
		$offset = $validator->isValid($this->getRequest()->getQuery("offset")) ? intval($this->getRequest()->getQuery("offset")) : 0;
		$limit = $validator->isValid($this->getRequest()->getQuery("limit")) ? intval($this->getRequest()->getQuery("limit")) : $this->getPageSize();
		
		$memberships = $this->getOrganizationService()->findOrganizationMemberships($this->organization, $limit, $offset);
		$totalMemberships = $this->getOrganizationService()->countOrganizationMemberships($this->organization);
		
		$view = new OrganizationMembershipJsonModel($this->url(), $this->identity());
		$view->setVariables(['organization' => $this->organization, 'resource' => $memberships, 'totalMemberships' => $totalMemberships]);
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
	
	public function setPageSize($size){
		if(is_int($size)){
			$this->pageSize = $size;
		}
	}
	
	public function getPageSize(){
		return $this->pageSize;
	}
}
