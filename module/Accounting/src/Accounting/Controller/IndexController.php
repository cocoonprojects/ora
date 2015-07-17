<?php
namespace Accounting\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
	public function indexAction()
	{
		$this->layout()->setVariable('orgId', $this->params('orgId'));
		$rv = new ViewModel();
		return $rv;
	}

}