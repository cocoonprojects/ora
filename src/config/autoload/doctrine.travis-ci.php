<?php
return array(
	'doctrine' => array(
		'connection' => array(
			'orm_default' => array(
				'params' => array(
					'host'			=> 'localhost',
					'port'			=> 3306,
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