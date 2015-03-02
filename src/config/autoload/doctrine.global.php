<?php
return array(
	'doctrine' => array(
		'connection' => array(
			'orm_default' => array(
				'params' => array(
					'host'			=> '127.0.0.1',
					'port'			=> '3306',
					'user'			=> 'travis',
					'password'		=> '',
					'dbname'		=> 'oraproject_test',
					'driverOptions' => array(
						\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
					),
				)
			)
		)
	)
);