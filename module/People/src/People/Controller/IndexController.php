<?php
namespace People\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{
	public function indexAction()
	{
		$this->layout()->setVariable('orgId', $this->params('orgId'));
	}

	public function organizationsAction()
	{

	}
}