<?php
namespace People\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Application\UserService;
use Application\Entity\User;
use People\View\UserProfileJsonModel;

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

	public function organizationsAction()
	{

	}
	
	public function profileAction()
	{
		$organizationService = $this->getServiceLocator()->get('People\OrganizationService');
		$organization = $organizationService->findOrganization($this->params('orgId'));
		if (is_null($organization)){
			$this->response->setStatusCode(404);
		}
		if(is_null($this->params('userId'))){
			$this->response->setStatusCode(400);
		}
				
		$userService = $this->getServiceLocator()->get('Application\UserService');
		$user = $userService->findUser($this->params('userId'));
		
		$this->layout()->setVariable('organization', $organization);
		$this->layout()->setVariable('user', $user);
	}
}