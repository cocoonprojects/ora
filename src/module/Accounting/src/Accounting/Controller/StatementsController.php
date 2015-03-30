<?php
namespace Accounting\Controller;

use Application\Controller\AuthController;

use ZendExtension\Mvc\Controller\AbstractHATEOASRestfulController;
use Ora\Accounting\AccountService;
use Accounting\View\StatementJsonModel;
use BjyAuthorize\Service\Authorize;

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
	 * @var Authorize
	 */
	private $authorize;
	
	public function __construct(AccountService $accountService, Authorize $authorize) {
		$this->accountService = $accountService;
		$this->authorize = $authorize;
	}

	public function get($id)
	{
		$account = $this->accountService->findAccount($id);
		if(is_null($account)) {
			$this->response->setStatusCode(404);
			return $this->response;
		}
		$identity = $this->identity()['user'];
		$viewModel = new StatementJsonModel($this->url(), $identity, $this->authorize);
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