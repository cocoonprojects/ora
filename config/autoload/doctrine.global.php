<?php
return array (
		'doctrine' => array (
				'configuration' => array (
						'orm_default' => array (
								'generate_proxies' => false,
								'proxy_dir' => __DIR__ . '/../../../data/DoctrineORMModule/Proxies/' 
						) 
				),
				'connection' => array (
						'orm_default' => array (
								'params' => array (
										'host' => getenv ( 'DB_HOSTNAME' ),
										'port' => getenv ( 'DB_PORT' ),
										'user' => getenv ( 'DB_USERNAME' ),
										'password' => getenv ( 'DB_PASSWORD' ),
										'dbname' => getenv ( 'DB_NAME' ),
										'driverOptions' => array (
												\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8' 
										) 
								) 
						) 
				) 
		) 
);