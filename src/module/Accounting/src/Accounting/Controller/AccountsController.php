<?php
namespace Accounting\Controller;

use Accounting\View\CreditsAccountJsonModel;
use ZendExtension\Mvc\Controller\AbstractHATEOASRestfulController;

class AccountsController extends AbstractHATEOASRestfulController
{
	protected static $collectionOptions = array ('GET','POST');
	protected static $resourceOptions = array ('DELETE','GET');
	protected $accountsService;
	
	// Gets my credits accounts list
	public function getList()
	{
		$rv = $this->getCreditsAccountsService()->listAccounts();
		$viewModel = new CreditsAccountJsonModel();
		$viewModel->setVariable('resource', $rv);
		$viewModel->setVariable('url', $this->url()->fromRoute('accounts'));
		return $viewModel;
	}

	// Creates my credits account for an organization if not already existing
	public function create($data)
	{
		// if JSON Content-Type, returns decoded data; for
		// application/x-www-form-urlencoded, returns array
		$this->getCreditsAccountsService()->create();
		$response = $this->getResponse();
		$response->setStatusCode(201); // Created
// 		$response->getHeaders()->addHeaderLine(
// 				'Location',
// 				$this->url()->fromRoute('accounts', array('id' => $resource->getId()))
// 		);
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
	
	protected function getCreditsAccountsService() {
		if (!isset($this->accountsService)) {
             $sm = $this->getServiceLocator();
             $this->accountsService = $sm->get('Accounting\CreditsAccountsService');
         }
         return $this->accountsService;
	}
}