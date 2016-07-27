<?php
namespace Accounting\Controller;

use Accounting\Service\AccountService;
use Accounting\View\StatementJsonModel;
use Application\Controller\OrganizationAwareController;
use People\Service\OrganizationService;
use Zend\I18n\Validator\IsInt;
use Zend\Permissions\Acl\Acl;
use Zend\Validator\GreaterThan;
use Zend\Validator\ValidatorChain;

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

	public function __construct(
		AccountService $accountService,
		Acl $acl,
		OrganizationService $organizationService)
	{
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

		$validator = new ValidatorChain();
		$validator->attach(new IsInt())
			->attach(new GreaterThan(['min' => 0, 'inclusive' => false]));

		$offset = $validator->isValid($this->getRequest()->getQuery("offset")) ? intval($this->getRequest()->getQuery("offset")) : 0;
		$limit = $validator->isValid($this->getRequest()->getQuery("limit")) ? intval($this->getRequest()->getQuery("limit")) : $this->organization->getParams()->get('personal_transaction_limit_per_page');

		$account = $this->accountService->findPersonalAccount($this->identity(), $this->organization);
		if(is_null($account)) {
			$this->response->setStatusCode(404);
			return $this->response;
		}

		if(!$this->isAllowed($this->identity(), $account, 'Accounting.Account.statement')) {
			$this->response->setStatusCode(403);
			return $this->response;
		}

		$transactions = $this->accountService->findTransactions($account, $limit, $offset);
		$totalTransactions = $this->accountService->countTransactions($account);

		$viewModel = new StatementJsonModel($this->url(), $this->identity(), $this->acl);
		$viewModel->setVariables(['resource'=>$account, 'transactions'=>$transactions, 'totalTransactions' => $totalTransactions]);
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
