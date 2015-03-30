<?php
namespace Accounting\Controller;

use ZendExtension\Mvc\Controller\AbstractHATEOASRestfulController;
use Zend\Authentication\AuthenticationServiceInterface;
use Ora\Accounting\AccountService;
use Accounting\View\AccountsJsonModel;
use BjyAuthorize\Service\Authorize;

class AccountsController extends AbstractHATEOASRestfulController
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
	 * @var Authorize
	 */
	private $authorize;
	
	public function __construct(AccountService $accountService, Authorize $authorize) {
		$this->accountService = $accountService;
		$this->authorize = $authorize;
	}
	
	// Gets my credits accounts list
	public function getList()
	{
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}
		
		$identity = $this->identity()['user'];
		$accounts = $this->accountService->findAccounts($identity);
		
		$viewModel = new AccountsJsonModel($this->url(), $identity, $this->authorize);
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
		$viewModel = new AccountsJsonModel($this->url(), $identity, $this->authorize);
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