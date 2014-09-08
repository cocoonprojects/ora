<?php
namespace Accounting;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Accounting\Model\CreditsAccountFactoryImpl;
use Zend\ModuleManager\ModuleManager;
use Ora\CreditsAccount\EventSourcingCreditsAccountsService;

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
				'Accounting\CreditsAccountsService' => function ($sm) {
							return new EventSourcingCreditsAccountsService();
						},
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
