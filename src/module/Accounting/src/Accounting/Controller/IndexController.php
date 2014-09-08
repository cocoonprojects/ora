<?php
namespace Accounting\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
	protected $accountsService;
	
	public function indexAction()
	{
		$a = $this->getCreditsAccountFactory()->create();
		$viewModel = new ViewModel();
		$viewModel->setVariable('accounts', array($a, $a));
		return $viewModel;
	}

	protected function getCreditsAccountFactory() {
		if (!$this->accountsService) {
             $sm = $this->getServiceLocator();
             $this->accountsService = $sm->get('Accounting\CreditsAccountsService');
         }
         return $this->accountsService;
	}
}