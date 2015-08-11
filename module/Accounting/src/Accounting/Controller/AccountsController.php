<?php
namespace Accounting\Controller;

use Accounting\Service\AccountService;
use Accounting\View\AccountsJsonModel;
use Application\Controller\OrganizationAwareController;
use Application\Service\UserService;
use People\Service\OrganizationService;
use Zend\Permissions\Acl\Acl;

class AccountsController extends OrganizationAwareController
{
	protected static $collectionOptions = ['GET'];
	protected static $resourceOptions = [];
	/**
	 * @var AccountService
	 */
	protected $accountService;
	/**
	 * @var Acl
	 */
	private $acl;
	/**
	 * @var UserService
	 */
	private $userService;
	
	public function __construct(AccountService $accountService, UserService $userService, Acl $acl, OrganizationService $organizationService) {
		parent::__construct($organizationService);
		$this->accountService = $accountService;
		$this->acl = $acl;
		$this->userService = $userService;
	}
	
	public function getList()
	{
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}
		$identity = $this->identity()['user'];
		
		if(!$this->isAllowed($identity, $this->organization, 'Accounting.Accounts.list')){
			$this->response->setStatusCode(403);
			return $this->response;
		}

		$email = $this->params()->fromQuery('email');
		if(!empty($email)) {
			$user = $this->userService->findUserByEmail($email);
			$account = $this->accountService->findPersonalAccount($user, $this->organization);
			$viewModel = new AccountsJsonModel($this->url(), $identity, $this->acl, $this->organization);
			$viewModel->setVariable('resource', [$account]);
			return $viewModel;
		}

		$this->response->setStatusCode(400);
		return $this->response;
	}

	public function getAccountService() {
		return $this->accountService;
	}
	
	protected function getCollectionOptions() {
		return self::$collectionOptions;
	}
	
	protected function getResourceOptions() {
		return self::$resourceOptions;
	}
}
