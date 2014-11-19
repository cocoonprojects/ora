<?php

namespace Test;

use Zend\Loader\AutoloaderFactory;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;
use RuntimeException;

error_reporting(E_ALL | E_STRICT);
chdir(dirname(__DIR__));

$path = __DIR__ . '/../src/vendor/zendframework/zendframework/library';
putenv("ZF2_PATH=".$path);

include __DIR__ . '/../src/init_autoloader.php';

/**
 * Test bootstrap, for setting up autoloading
 */
class Bootstrap
{
    protected static $serviceManager;
	protected static $config;
	protected static $entityManager;
	protected static $schemaTool;

    public static function init($config)
    {
    	putenv("APPLICATION_ENV=acceptance");
        $zf2ModulePaths = array(dirname(dirname(__DIR__)));
        if (($path = static::findParentPath('vendor'))) {
            $zf2ModulePaths[] = $path;
        }
        if (($path = static::findParentPath('module')) !== $zf2ModulePaths[0]) {
            $zf2ModulePaths[] = $path;
        }

        //static::initAutoloader();

        // use ModuleManager to load this module and it's dependencies
 		$config = ArrayUtils::merge($config, include(__DIR__.'/../src/config/application.config.php'));
        
        $serviceManager = new ServiceManager(new ServiceManagerConfig());
        $serviceManager->setService('ApplicationConfig', $config);
        $serviceManager->get('ModuleManager')->loadModules();
        static::$serviceManager = $serviceManager;
        static::$config = $config;
        self::deleteDatabase();
        self::setupDatabase();
    }

    public static function getServiceManager()
    {
        return static::$serviceManager;
    }

    public static function getConfig()
    {
        return static::$config;
    }

    protected static function initAutoloader()
    {
        $vendorPath = static::findParentPath('vendor');

        $zf2Path = getenv('ZF2_PATH');
        if (!$zf2Path) {
            if (defined('ZF2_PATH')) {
                $zf2Path = ZF2_PATH;
            } elseif (is_dir($vendorPath . '/ZF2/library')) {
                $zf2Path = $vendorPath . '/ZF2/library';
            } elseif (is_dir($vendorPath . '/zendframework/zendframework/library')) {
                $zf2Path = $vendorPath . '/zendframework/zendframework/library';
            }
        }

        if (!$zf2Path) {
            throw new RuntimeException('Unable to load ZF2. Run `php composer.phar install` or define a ZF2_PATH environment variable.');
        }

        include $zf2Path . '/Zend/Loader/AutoloaderFactory.php';
        AutoloaderFactory::factory(array(
            'Zend\Loader\StandardAutoloader' => array(
                'autoregister_zf' => true,
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/' . __NAMESPACE__,
                ),
            ),
        ));
    }

    protected static function findParentPath($path)
    {
        $dir = __DIR__;
        $previousDir = '.';
        while (!is_dir($dir . '/' . $path)) {
            $dir = dirname($dir);
            if ($previousDir === $dir) return false;
            $previousDir = $dir;
        }
        return $dir . '/' . $path;
    }

    public static function deleteDatabase(){
    	//drop tables creates by doctrine
    	$classes = self::$entityManager->getMetadataFactory()->getAllMetadata();
    	self::$schemaTool->dropSchema($classes);
    
    	//drop event_stream table
    	$sql_drop_event_store = "drop table if exists event_stream";
    	$statement_del = self::$entityManager->getConnection()->executeUpdate($sql_drop_event_store, array(), array());
    }
    
    public static function setupDatabase(){
    	//get all doctrine metadata for create schema
    	$classes = self::$entityManager->getMetadataFactory()->getAllMetadata();
    	self::$schemaTool->createSchema($classes);
    
    	//get query for event_store table creation
    	$sql = file_get_contents(__DIR__."/../src/vendor/prooph/event-store-zf2-adapter/scripts/mysql-single-stream-default-schema.sql");
    	$statement = self::$entityManager->getConnection()->prepare($sql);
    	$statement->execute();
    	$statement->closeCursor(); //needed for mysql database
    
    	//get query for test data
    	$sql = file_get_contents(__DIR__."/sql/init.sql");
    	$statement = self::$entityManager->getConnection()->executeUpdate($sql, array(), array());
    }
    
}