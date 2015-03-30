<?php

namespace Test;

use Zend\Loader\AutoloaderFactory;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;
use RuntimeException;
use Zend\Stdlib\ArrayUtils;
use Zend\Mvc\Application;

error_reporting(E_ALL | E_STRICT);
chdir(dirname(__DIR__));

$path = __DIR__ . '/../src/vendor/zendframework/zendframework/library';
putenv("ZF2_PATH=".$path);

include __DIR__ . '/../src/init_autoloader.php';
if(isset($loader)) {
	$loader->set('Prooph\\EventStoreTest\\', __DIR__ . '/../src/vendor/prooph/event-store/tests');
}

/**
 * Test bootstrap, for setting up autoloading
 */
class Bootstrap
{
    protected static $serviceManager;
	protected static $config;
	protected static $entityManager;
	protected static $schemaTool;
	protected static $zendApp;

    public static function init($config)
    {
    	putenv('APPLICATION_ENV=acceptance');
        self::$zendApp = Application::init(include(__DIR__.'/../src/config/application.config.php')); //new application instance
	
        $serviceManager = self::$zendApp->getServiceManager();		
 		$config = ArrayUtils::merge($config, include(__DIR__.'/../src/config/application.config.php'));
        
        $serviceManager = new ServiceManager(new ServiceManagerConfig());
        $serviceManager->setService('ApplicationConfig', $config);
        $serviceManager->get('ModuleManager')->loadModules();
        static::$serviceManager = $serviceManager;
        static::$config = $config;
		static::$entityManager = $serviceManager->get('doctrine.entitymanager.orm_default');
		static::$schemaTool = new \Doctrine\ORM\Tools\SchemaTool(static::$entityManager);
        static::deleteDatabase();
        static::setupDatabase();
    }

    public static function getServiceManager()
    {
        return static::$serviceManager;
    }
    
    public static function getEventManager()
    {
    	return static::$zendApp->getEventManager();
    }

    public static function getConfig()
    {
        return static::$config;
    }

    public static function deleteDatabase(){
    	//drop tables creates by doctrine
    	$classes = static::$entityManager->getMetadataFactory()->getAllMetadata();
    	static::$schemaTool->dropSchema($classes);
    
    	//drop event_stream table
    	$sql_drop_event_store = "drop table if exists event_stream";
    	$statement_del = static::$entityManager->getConnection()->executeUpdate($sql_drop_event_store, array(), array());
    }
    
    public static function setupDatabase(){
    	//get all doctrine metadata for create schema
    	$classes = static::$entityManager->getMetadataFactory()->getAllMetadata();
    	static::$schemaTool->createSchema($classes);
    
    	//get query for event_store table creation
    	$sql = file_get_contents(__DIR__."/../src/vendor/prooph/event-store-zf2-adapter/scripts/mysql-single-stream-default-schema.sql");
    	$statement = static::$entityManager->getConnection()->prepare($sql);
    	$statement->execute();
    	$statement->closeCursor(); //needed for mysql database
    
    	//get query for test data
    	$sql = file_get_contents(__DIR__."/sql/init.sql");
    	$statement = static::$entityManager->getConnection()->executeUpdate($sql, array(), array());
    }
    
}

Bootstrap::init(include 'unit/test.config.php');