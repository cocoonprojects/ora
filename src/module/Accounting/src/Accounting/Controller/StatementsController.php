<?php
namespace Accounting\Controller;

use ZendExtension\Mvc\Controller\AbstractHATEOASRestfulController;
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

	public function __construct(AccountService $accountService) {
		$this->accountService = $accountService;
	}

	public function get($id)
	{
		$account = $this->accountService->findAccount($id);
		if(is_null($account)) {
			$this->response->setStatusCode(404);
			return $this->response;
		}
		$identity = $this->identity()['user'];
		$viewModel = new StatementJsonModel($this->url(), $identity);
		$viewModel->setVariable('resource', $account);
		return $viewModel;
	}

	protected function getCollectionOptions() {
		return self::$collectionOptions;
	}
	
	protected function getResourceOptions() {
		return self::$resourceOptions;
	}
	
}