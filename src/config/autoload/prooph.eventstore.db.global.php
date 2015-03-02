<?php
/*
 * This file is part of the codeliner/ProophEventStoreModule.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 20.07.14 - 15:34
 */

/**
 * ProophEventStore Db Adapter Configuration
 *
 * If you have a ./config/autoload/ directory set up for your project, you can
 * drop this config file in it and change the values as you wish.
 *
 * NOTE: Please make sure that you do not commit this file to your CVS cause it can contain sensitive data.
 */
/**
 * Start of ProophEventStore Adapter Configuration
 */
$adapter = array(
    /**
     * Adapter Type
     *
     * Specify the adapter that ProophEventStore should use to persist events
     */
    'type' => 'Prooph\EventStore\Adapter\Zf2\Zf2EventStoreAdapter',
    /**
     * Adapter options
     *
     * Specify configuration options for the adapter.
     * If you want to set up an own persistent adapter for the EventStore than pass the connection params to the underlying
     * adapter with the help of the options key. The structure of the options array depends on the used adapter type.
     *
     * Default value: SQLite in memory connection config for Zend\Db\Adapter\Adapter.
     *
     * Note: In most cases the default config is only useful for UnitTesting,
     */
    'options' => array(
        'connection' => array(
            'driver' => 'Pdo_Mysql',
    		'hostname' => 'localhost',
    		'port' 	   => '3306',
    		'username' => 'travis',
    		'password' => '',
    		'database' => 'oraproject_test'
		),
        //It's also possible to specify an DI alias for Zend\Db\Adapter\Adapter instead of configure a connection.
        //'zend_db_adapter' => 'Zend\Db\Adapter\Adapter',
    ),
    /**
     * End of ProophEventStore Adapter Configuration
     */
);

/* DO NOT EDIT BELOW THIS LINE */

return array(
    'prooph.event_store' => array(
        'adapter' => $adapter,
    )
);