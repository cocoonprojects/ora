<?php
namespace People\Controller;

use Zend\Mvc\Controller\AbstractActionController;

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

	public function organizationsAction()
	{

	}
}