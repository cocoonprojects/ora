<?php
namespace Accounting\Controller;

use Zend\Permissions\Acl\Acl;
use Application\Controller\AbstractHATEOASRestfulController;
use Accounting\Service\AccountService;
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
	 * @var Acl
	 */
	private $acl;
	
	public function __construct(AccountService $accountService, Acl $acl) {
		$this->accountService = $accountService;
		$this->acl = $acl;
	}

	public function get($id)
	{
		$account = $this->accountService->findAccount($id);
		if(is_null($account)) {
			$this->response->setStatusCode(404);
			return $this->response;
		}
		$identity = $this->identity()['user'];
		$viewModel = new StatementJsonModel($this->url(), $identity, $this->acl);
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