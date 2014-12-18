<?php
namespace Accounting\Controller;

use Accounting\View\CreditsAccountJsonModel;
use ZendExtension\Mvc\Controller\AbstractHATEOASRestfulController;
use Rhumsaa\Uuid\Uuid;
use Ora\Accounting\AccountService;

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
		if(!$this->authService->hasIdentity()) {
			$this->response->setStatusCode(401);
			return $this->response;
		}
		
		$identity = $this->authService->getIdentity()['user'];
		$accounts = $this->accountService->findAccounts($identity);
		
		$viewModel = new CreditsAccountJsonModel();
		$viewModel->setVariable('resource', $accounts);
		$viewModel->setVariable('url', $this->url()->fromRoute('accounts'));
		return $viewModel;
	}

	// Creates my credits account for an organization if not already existing
	public function create($data)
	{
		
		// if JSON Content-Type, returns decoded data; for
		// application/x-www-form-urlencoded, returns array
		$this->accountService->create();
		$response = $this->getResponse();
		$response->setStatusCode(201); // Created
		$response->getHeaders()->addHeaderLine(
				'Location',
				$this->url()->fromRoute('accounts', array('id' => $resource->getId()))
		);
		return $response;
	}
	
	// Deletes an existing credits account
	public function delete($id)
	{
		
	}
	
	// Get the Bank Statement
	public function get($id)
	{
		$rv = $this->getCreditsAccountsService()->getAccount($id);
		$viewModel = new CreditsAccountJsonModel();
		$viewModel->setVariable('resource', $rv);
		return $viewModel;
	}
	
	protected function getCollectionOptions() {
		return self::$collectionOptions;
	}
	
	protected function getResourceOptions() {
		return self::$resourceOptions;
	}
	
	protected function getJsonModelClass(){
		return $this->jsonModelClass;
	}
	
}