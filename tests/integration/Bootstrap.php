<?php

namespace IntegrationTest;

use Zend\Loader\AutoloaderFactory;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;
use RuntimeException;
use Zend\Mvc\Application;
use Doctrine\ORM\Tools\SchemaTool;

error_reporting(E_ALL | E_STRICT);
chdir(dirname(__DIR__));

$path = __DIR__ . '/../../src/vendor/zendframework/zendframework/library';
putenv("ZF2_PATH=".$path);

include __DIR__ . '/../../src/init_autoloader.php';

/**
 * Test bootstrap, for setting up autoloading
 */
class Bootstrap
{
	protected static $serviceManager;
	protected static $config;
	protected static $zendApp;

	public static function init($config)
	{
		self::$zendApp = Application::init($config);
		self::$serviceManager = self::$zendApp->getServiceManager();		
		static::$config = $config;
		
		$entityManager = self::$serviceManager->get('doctrine.entitymanager.orm_default');
		$schemaTool = new SchemaTool($entityManager);
		$metadata = $entityManager->getMetadataFactory()->getAllMetadata();
		$schemaTool->dropSchema($metadata);
		$schemaTool->updateSchema($metadata);
		$entityManager->getConnection()->executeUpdate(file_get_contents(__DIR__."/../sql/init.sql"), array(), array());
	}

	public static function getServiceManager()
	{
		return static::$serviceManager;
	}
	
	public static function getConfig()
	{
		return static::$config;
	}
}

Bootstrap::init(include __DIR__.'/../../src/config/application.config.php');