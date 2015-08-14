<?php
namespace Accounting\Controller;

use Accounting\Service\AccountService;
use Accounting\View\StatementJsonModel;
use Application\Controller\OrganizationAwareController;
use People\Service\OrganizationService;
use Zend\Permissions\Acl\Acl;

class PersonalStatementController extends OrganizationAwareController
{
	protected static $collectionOptions = ['GET'];
	protected static $resourceOptions   = [];
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
	
	public function __construct(AccountService $accountService, Acl $acl, OrganizationService $organizationService) {
		parent::__construct($organizationService);
		$this->accountService = $accountService;
		$this->acl = $acl;
	}

	public function getList()
	{
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}

		$account = $this->accountService->findPersonalAccount($this->identity(), $this->organization);
		if(is_null($account)) {
			$this->response->setStatusCode(404);
			return $this->response;
		}

		if(!$this->isAllowed($this->identity(), $account, 'Accounting.Account.statement')) {
			$this->response->setStatusCode(403);
			return $this->response;
		}

		$viewModel = new StatementJsonModel($this->url(), $this->identity(), $this->acl);
		$viewModel->setVariable('resource', $account);
		return $viewModel;
	}

	/**
	 * @return AccountService
	 * @codeCoverageIgnore
	 */
	public function getAccountService()
	{
		return $this->accountService;
	}

	/**
	 * @return array
	 * @codeCoverageIgnore
	 */
	protected function getCollectionOptions() {
		return self::$collectionOptions;
	}

	/**
	 * @return array
	 * @codeCoverageIgnore
	 */
	protected function getResourceOptions() {
		return self::$resourceOptions;
	}
	
}
