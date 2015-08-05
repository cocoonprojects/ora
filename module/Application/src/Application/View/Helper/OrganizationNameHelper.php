<?php 

namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use People\Entity\Organization;

class OrganizationNameHelper extends AbstractHelper implements ServiceLocatorAwareInterface{
	/**
	 *
	 * @var ServiceLocatorInterface
	 */
	private $serviceLocator;
	public function __invoke() {
		$output = 'O.R.A.';
		
		$organizationService = $this->getServiceLocator()->get('People\OrganizationService');
		$request = $this->getServiceLocator()->get('Request');
		$router = $this->getServiceLocator()->get('Router');
		$routeMatch = $router->match($request);
		
		if($routeMatch){
			$orgId = $routeMatch->getParam('orgId');
			if($orgId){
				$organization = $organizationService->findOrganization($orgId);
				if($organization instanceof Organization){
					$output = $organization->getName();
				}
			}
		}
		
		return $output;
	}
	
	public function setServiceLocator(ServiceLocatorInterface $helperPluginManager){
		$this->serviceLocator = $helperPluginManager->getServiceLocator();
		return $this;
	}
	
	public function getServiceLocator(){
		return $this->serviceLocator;
	}
}