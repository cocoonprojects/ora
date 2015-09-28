<?php
namespace Accounting\Controller;

use Accounting\Service\AccountService;
use Accounting\View\StatementJsonModel;
use Application\Controller\OrganizationAwareController;
use People\Service\OrganizationService;
use Zend\Permissions\Acl\Acl;
use Zend\I18n\Validator\Int;
use Zend\Validator\ValidatorChain;
use Zend\Validator\GreaterThan;

class PersonalStatementController extends OrganizationAwareController
{
	const DEFAULT_TRANSACTIONS_LIMIT = 10;
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
	/**
	 *
	 * @var integer
	 */
	protected $transactionsLimit = self::DEFAULT_TRANSACTIONS_LIMIT;
	
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
		
		$validator = new ValidatorChain();
		$validator->attach(new Int())
			->attach(new GreaterThan(['min' => 0, 'inclusive' => false]));
		
		$offset = $validator->isValid($this->getRequest()->getQuery("offset")) ? intval($this->getRequest()->getQuery("offset")) : 0;
		$limit = $validator->isValid($this->getRequest()->getQuery("limit")) ? intval($this->getRequest()->getQuery("limit")) : $this->getTransactionsLimit();
		
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
	
	public function setTransactionsLimit($size){
		if(is_int($size)){
			$this->transactionsLimit = $size;
		}
	}
	
	public function getTransactionsLimit(){
		return $this->transactionsLimit;
	}
	
}
