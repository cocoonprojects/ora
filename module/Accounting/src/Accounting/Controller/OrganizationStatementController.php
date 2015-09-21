<?php
namespace Accounting\Controller;

use Accounting\View\StatementJsonModel;
use Application\Service\UserService;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Permissions\Acl\Acl;
use Accounting\Service\AccountService;
use Accounting\View\AccountsJsonModel;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\EventManagerInterface;
use Application\Controller\OrganizationAwareController;
use People\Service\OrganizationService;
use Zend\Validator\NotEmpty;
use Zend\I18n\Validator\Int;
use Zend\Validator\ValidatorChain;
use Zend\Validator\GreaterThan;

class OrganizationStatementController extends OrganizationAwareController
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
	/**
	 *
	 * @var integer
	 */
	protected $pageSize;
	
	public function __construct(AccountService $accountService, Acl $acl, OrganizationService $organizationService) {
		parent::__construct($organizationService);
		$this->accountService = $accountService;
		$this->acl = $acl;
		$this->pageSize = 10;
	}
	
	public function getList()
	{
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}
		
		$validator = new ValidatorChain();
		$validator->attach(new NotEmpty())
			->attach(new Int())
			->attach(new GreaterThan(['min' => 0, 'inclusive' => false]));
		
		$offset = $validator->isValid($this->getRequest()->getQuery("offset")) ? intval($this->getRequest()->getQuery("offset")) : 0;
		$limit = $validator->isValid($this->getRequest()->getQuery("limit")) ? intval($this->getRequest()->getQuery("limit")) : $this->getPageSize();

		$account = $this->accountService->findOrganizationAccount($this->organization);
		if(is_null($account)) {
			$this->response->setStatusCode(404);
			return $this->response;
		}

		if(!$this->isAllowed($this->identity(), $account, 'Accounting.Account.statement')) {
			$this->response->setStatusCode(403);
			return $this->response;
		}

		$viewModel = new StatementJsonModel($this->url(), $this->identity(), $this->acl);
		$viewModel->setVariables(['resource'=>$account, 'offset'=>$offset, 'limit'=>$limit]);
		return $viewModel;
	}

	/**
	 * @return AccountService
	 * @codeCoverageIgnore
	 */
	public function getAccountService() {
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
	
	public function setPageSize($size){
		if(is_int($size)){
			$this->pageSize = $size;
		}
	}
	
	public function getPageSize(){
		return $this->pageSize;
	}
	
}
