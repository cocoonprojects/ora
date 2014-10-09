<?php

    $dbParams = array(
        'hostname' => 'localhost',
        'port' => 3306,
        'username' => 'ora',
        'password' => 'ora_DB!',
        'database' => 'oraproject_test'
    );
    
    return array(
    		'zendoauth2' => array(
    				'google' => array(
    						'client_id'     => '',
    						'client_secret' => '',
    						'redirect_uri'  => 'http://localhost/auth/login/google',
    				),
    				'linkedin' => array(
    						'client_id'     => '',
    						'client_secret' => '',
    						'redirect_uri'  => 'http://localhost/auth/login/linkedin',
    				),
    				'testProvider' => array(
    						'client_id'     => '',
    						'client_secret' => '',
    						'redirect_uri'  => 'http://localhost/auth/login/testProvider',
    						'auth_uri'      => 'http://oraprojecttest/auth/login/testprovider',
    				),
    		),
    		    		
            'doctrine' => array(
                    'connection' => array(
                            'orm_default' => array(
                                    'params' => array(
                                            'host' => $dbParams['hostname'],
                                            'port' => $dbParams['port'],
                                            'user' => $dbParams['username'],
                                            'password' => $dbParams['password'],
                                            'dbname' => $dbParams['database'],
                                            'driverOptions' => array(
                                                    \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
                                            ),
                                    )
                            )
                    )
            )
    );