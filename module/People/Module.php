<?php
namespace People;

use AcMailer\View\DefaultLayout;
use People\Controller\OrganizationsController;
use People\Controller\MembersController;
use People\Service\EventSourcingOrganizationService;
use People\Service\OrganizationCommandsListener;
use People\Service\SendMailListener;
use People\Controller\UserProfileController;

class Module
{
	public function getControllerConfig() 
	{
		return array(
			'invokables' => array(
				'People\Controller\Index' => 'People\Controller\IndexController',
			),
			'factories' => array(
				'People\Controller\Organizations' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$orgService = $locator->get('People\OrganizationService');
					$controller = new OrganizationsController($orgService);
					return $controller;
				},
				'People\Controller\Members' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$orgService = $locator->get('People\OrganizationService');
					$controller = new MembersController($orgService);
					if(array_key_exists('default_members_limit', $locator->get('Config'))){
						$size = $locator->get('Config')['default_members_limit'];
						$controller->setListLimit($size);
					}
					return $controller;
				},
				'People\Controller\UserProfile' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$orgService = $locator->get('People\OrganizationService');
					$userService = $locator->get('Application\UserService');
					$accountService = $locator->get('Accounting\CreditsAccountsService');
					$controller = new UserProfileController($orgService, $userService, $accountService);
					return $controller;
				},
			)
		);
	}
	
	public function getServiceConfig()
	{
		return array(
			'invokables' => array(
			),
			'factories' => array(
				'People\OrganizationService' => function ($serviceLocator) {
					$eventStore = $serviceLocator->get('prooph.event_store');
					$entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
					return new EventSourcingOrganizationService($eventStore, $entityManager);
				},
				'People\OrganizationCommandsListener' => function ($serviceLocator) {
					$entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
					return new OrganizationCommandsListener($entityManager);
				},
				'People\SendMailListener' => function ($serviceLocator) {
					$mailService = $serviceLocator->get('AcMailer\Service\MailService');
					$userService = $serviceLocator->get('Application\UserService');
					$organizationService = $serviceLocator->get('People\OrganizationService');
					return new SendMailListener($mailService, $userService, $organizationService);
				}
			),
		);
	}
	
	public function getConfig()
	{
		return include __DIR__ . '/config/module.config.php';
	}
	
	public function getAutoloaderConfig()
	{		
		return array(
			'Zend\Loader\StandardAutoloader' => array(
				'namespaces' => array(
					__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
				),
			),
		);
	}
}