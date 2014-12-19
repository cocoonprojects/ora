<?php
namespace Accounting\Controller;

use ZendExtension\Mvc\Controller\AbstractHATEOASRestfulController;
use Zend\Authentication\AuthenticationServiceInterface;
use Ora\Accounting\AccountService;
use Accounting\View\StatementJsonModel;

class StatementsController extends AbstractHATEOASRestfulController
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
	 * @var AuthenticationServiceInterface
	 */
	protected $authService;

	public function __construct(AccountService $accountService, AuthenticationServiceInterface $authService) {
		$this->accountService = $accountService;
		$this->authService = $authService;
	}

	public function get($id)
	{
		$this->account = $this->accountService->findAccount($id);
		if(is_null($this->account)) {
			$this->response->setStatusCode(404);
			return $this->response;
		}
		$viewModel = new StatementJsonModel($this->url());
		$viewModel->setVariable('resource', $this->account);
		return $viewModel;
	}

	protected function getCollectionOptions() {
		return self::$collectionOptions;
	}
	
	protected function getResourceOptions() {
		return self::$resourceOptions;
	}
	
}