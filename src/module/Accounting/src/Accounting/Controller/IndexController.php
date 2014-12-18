<?php
namespace Accounting\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Ora\CreditsAccount\CreditsAccountsService;
use Ora\Accounting\AccountService;
use Zend\Authentication\AuthenticationServiceInterface;

class IndexController extends AbstractActionController
{
	/**
	 * 
	 * @var AccountService
	 */
	protected $accountService;
	/**
	 * 
	 * @var AuthenticationServiceInterface
	 */
	private $authService;
	
	public function __construct(AccountService $accountService, AuthenticationServiceInterface $authService) {
		$this->accountService = $accountService;
		$this->authService = $authService;
	}
	
	public function indexAction()
	{
		$rv = new ViewModel();
		if($this->authService->hasIdentity()) {
			$identity = $this->authService->getIdentity()['user'];
			
			$accounts = $this->accountService->findAccounts($identity);
			$rv->setVariable('accounts', $accounts);
		}
		return $rv;
	}

}