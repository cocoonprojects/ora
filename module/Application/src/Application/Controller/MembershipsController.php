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

/**
 * Class MembershipsController
 * @package Application\Controller
 * TODO: rename into IdentityController
 */
class MembershipsController extends HATEOASRestfulController
{
	protected static $collectionOptions = ['GET'];
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

		$memberships = $this->orgService->findUserOrganizationMemberships($this->identity());
		
		$view = new OrganizationMembershipJsonModel($this->url(), $this->identity());
		$view->setVariable('resource', $memberships);
		return $view;
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