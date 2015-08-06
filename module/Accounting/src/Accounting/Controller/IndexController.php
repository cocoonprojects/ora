<?php
namespace Accounting\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{	
	public function indexAction()
	{
		$organizationService = $this->getServiceLocator()->get('People\OrganizationService');		
		$organization = $organizationService->findOrganization($this->params('orgId'));
		if (is_null($organization)){
			$this->response->setStatusCode(404);
		}
		$this->layout()->setVariable('orgId', $this->params('orgId'));
		$this->layout()->setVariable('orgName', $organization->getName());
	}

}