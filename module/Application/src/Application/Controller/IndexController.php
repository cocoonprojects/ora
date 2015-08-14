<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
	public function indexAction()
	{
		$sl = $this->getServiceLocator();
		$adapter = $sl->get('Application\Service\AdapterResolver');
		$providers = $adapter->getProviders();
		return new ViewModel([
			'providers' => $providers
		]);
	}
}
