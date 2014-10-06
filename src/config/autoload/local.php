<?php
 
    $dbParams = array(
        'hostname' => 'localhost',
        'port' => 3306,
        'username' => 'ora',
        'password' => 'ora_DB!',
        'database' => 'oraproject'
    );
    
    return array(
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