<?php
namespace Accounting;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\ModuleManager;
use Ora\Accounting\EventSourcingCreditsAccountsService;
use Ora\EventStore\DoctrineEventStore;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{
	
	public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
         return array(
             'Zend\Loader\ClassMapAutoloader' => array(
                 __DIR__ . '/autoload_classmap.php',
             ),
             'Zend\Loader\StandardAutoloader' => array(
                 'namespaces' => array(
                     __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                 		 
						) 
				) 
		);
	}
	
	public function getServiceConfig()
	{
		return array (
			'factories' => array (
				'Accounting\CreditsAccountsService' => 'Accounting\Service\CreditsAccountsServiceFactory',
     			),
     	);
	}

	public function init(ModuleManager $mm)
	{
		$mm->getEventManager()->getSharedManager()->attach(__NAMESPACE__,
			'dispatch', function($e) {
				$e->getTarget()->layout('accounting/layout');
			});
	}
}
