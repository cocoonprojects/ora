<?php
namespace TaskManagement\Controller;

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
		$this->layout()->setVariable('organization', $organization);
	}
}