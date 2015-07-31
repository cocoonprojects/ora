<?php
namespace Accounting\Controller;

use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Permissions\Acl\Acl;
use ZFX\Rest\Controller\HATEOASRestfulController;
use Accounting\Service\AccountService;
use Accounting\View\AccountsJsonModel;
use People\Service\OrganizationService;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\EventManagerInterface;
use People\Entity\Organization;

class AccountsController extends HATEOASRestfulController
{
	protected static $collectionOptions = ['GET'];
	protected static $resourceOptions = ['GET'];
	/**
	 * 
	 * @var AccountService
	 */
	protected $accountService;
	/**
	 * 
	 * @var Acl
	 */
	private $acl;
	/**
	 *
	 * @var OrganizationService
	 */
	private $organizationService;
	/**
	 *
	 * @var Organization
	 */
	private $organization;
	
	public function __construct(AccountService $accountService, Acl $acl, OrganizationService $organizationService) {
		$this->accountService = $accountService;
		$this->acl = $acl;
		$this->organizationService = $organizationService;
	}
	
	// Gets my credits accounts list
	public function getList()
	{
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}
		
		$identity = $this->identity()['user'];
		
		if(!$this->isAllowed($identity, $this->organization, 'Accounting.Accounts.list')){
			$this->response->setStatusCode(403);
			return $this->response;
		}
		
		$accounts = $this->accountService->findAccounts($identity, $this->organization);
		
		$viewModel = new AccountsJsonModel($this->url(), $identity, $this->acl);
		$viewModel->setVariable('resource', $accounts);
		return $viewModel;
	}

	public function get($id)
	{
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}
		
		$identity = $this->identity()['user'];
		$rv = $this->accountService->findAccount($id);
		if(is_null($rv)) {
			$this->response->setStatusCode(404);
			return $this->response;
		}
		$viewModel = new AccountsJsonModel($this->url(), $identity, $this->acl);
		$viewModel->setVariable('resource', $rv);
		return $viewModel;
	}
	
	protected function getCollectionOptions() {
		return self::$collectionOptions;
	}
	
	protected function getResourceOptions() {
		return self::$resourceOptions;
	}
	
	public function setEventManager(EventManagerInterface $events)
	{
		parent::setEventManager($events);
	
		// Register a listener at high priority
		$events->attach('dispatch', array($this, 'getOrganization'), 50);
	}
	
	public function getOrganization(MvcEvent $e){
	
		$orgId = $this->params('orgId');
		$response = $this->getResponse();
	
		if (is_null($orgId)){
			$response->setStatusCode(400);
			return $response;
		}
	
		$this->organization = $this->organizationService->findOrganization($orgId);
		if (is_null($this->organization)){
			$response->setStatusCode(404);
			return $response;
		}
	
		return;
	}
	
}