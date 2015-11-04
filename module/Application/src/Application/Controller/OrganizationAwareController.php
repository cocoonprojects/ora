<?php 

namespace Application\Controller;

use ZFX\Rest\Controller\HATEOASRestfulController;
use People\Service\OrganizationService;
use People\Entity\Organization;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\EventManagerInterface;

abstract class OrganizationAwareController extends HATEOASRestfulController{
	
	/**
	 *
	 * @var OrganizationService
	 */
	private $organizationService;
	/**
	 *
	 * @var Organization
	 */
	protected $organization;
	
	public function __construct(OrganizationService $organizationService)
	{
		$this->organizationService = $organizationService;
	}
	
	public function setEventManager(EventManagerInterface $events)
	{
		parent::setEventManager($events);
	
		// Register a listener at high priority
		$events->attach('dispatch', array($this, 'loadOrganization'), 50);
	}
	
	public function getOrganizationService(){
		
		return $this->organizationService;
	}
	
	public function loadOrganization(MvcEvent $e){

		$response = $this->getResponse();

		$orgId = $this->params('orgId');
		if(is_null($orgId)) {
			$response->setStatusCode(400);
			return $response;
		}

		$this->organization = $this->getOrganizationService()->findOrganization($orgId);
		if (is_null($this->organization)) {
			$response->setStatusCode(404);
			return $response;
		}
	}
}