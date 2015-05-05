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

$path = __DIR__ . '/../../vendor/zendframework/zendframework/library';
putenv("ZF2_PATH=".$path);

include __DIR__ . '/../../init_autoloader.php';

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
		echo shell_exec(__DIR__ . '/../../vendor/bin/doctrine-module orm:schema-tool:drop --force');
		echo shell_exec(__DIR__ . '/../../vendor/bin/doctrine-module orm:schema-tool:create');
		echo shell_exec(__DIR__ . '/../../vendor/bin/doctrine-module dbal:import ' . __DIR__ . '/../sql/init.sql');
		
		self::$zendApp = Application::init($config);
		self::$serviceManager = self::$zendApp->getServiceManager();
		static::$config = $config;
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

Bootstrap::init(include __DIR__.'/../../config/application.config.php');
