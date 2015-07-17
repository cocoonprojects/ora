<?php
namespace Accounting\Controller;

use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Permissions\Acl\Acl;
use ZFX\Rest\Controller\HATEOASRestfulController;
use Accounting\Service\AccountService;
use Accounting\View\AccountsJsonModel;

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
	
	public function __construct(AccountService $accountService, Acl $acl) {
		$this->accountService = $accountService;
		$this->acl = $acl;
	}
	
	// Gets my credits accounts list
	public function getList()
	{
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}
		
		$identity = $this->identity()['user'];
		$orgId = $this->params('orgId');
		
		if (is_null($orgId)){
			$this->response->setStatusCode(400);
			return $this->response;
		}
		
		$accounts = $this->accountService->findAccounts($identity);
		
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
	
}