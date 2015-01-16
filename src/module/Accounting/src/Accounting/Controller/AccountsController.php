<?php
namespace Accounting\Controller;

use ZendExtension\Mvc\Controller\AbstractHATEOASRestfulController;
use Zend\Authentication\AuthenticationServiceInterface;
use Ora\Accounting\AccountService;
use Accounting\View\AccountsJsonModel;

class AccountsController extends AbstractHATEOASRestfulController
{
	protected static $collectionOptions = ['GET'];
	protected static $resourceOptions = ['GET'];
	/**
	 * 
	 * @var AccountService
	 */
	protected $accountService;
	
	public function __construct(AccountService $accountService) {
		$this->accountService = $accountService;
	}
	
	// Gets my credits accounts list
	public function getList()
	{
		if(!$this->identity()) {
			$this->response->setStatusCode(401);
			return $this->response;
		}
		
		$identity = $this->loggedIdentity();
		$accounts = $this->accountService->findAccounts($identity);
		
		$viewModel = new AccountsJsonModel($this->url(), $identity);
		$viewModel->setVariable('resource', $accounts);
		return $viewModel;
	}

	public function get($id)
	{
		if(!$this->identity()) {
			$this->response->setStatusCode(401);
			return $this->response;
		}
		
		$identity = $this->identity()['user'];
		$rv = $this->accountService->findAccount($id);
		if(is_null($rv)) {
			$this->response->setStatusCode(404);
			return $this->response;
		}
		$viewModel = new AccountsJsonModel($this->url(), $identity);
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