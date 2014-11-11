<?php
namespace Accounting\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Ora\CreditsAccount\CreditsAccountsService;

class IndexController extends AbstractActionController
{
	/**
	 * 
	 * @var CreditsAccountsService
	 */
	protected $accountsService;
	
	public function indexAction()
	{
// 		$a = $this->getCreditsAccountFactory()->listAccounts();
// 		$viewModel = new ViewModel();
// 		$viewModel->setVariable('accounts', $a);
// 		return $viewModel;
	}

	protected function getCreditsAccountFactory() {
		if (!$this->accountsService) {
             $sm = $this->getServiceLocator();
             $this->accountsService = $sm->get('Accounting\CreditsAccountsService');
         }
         return $this->accountsService;
	}
}