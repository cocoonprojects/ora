<?php

return array(
	'doctrine' => array(
		'connection' => array(
			'orm_default' => array(
				'params' => array(
					'host'			=> 'localhost',
					'port'			=> 3306,
					'user'			=> 'ora',
					'password'		=> 'ora_DB!',
					'dbname'		=> 'oraproject',
					'driverOptions' => array(
						\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
					),
				)
			)
		)
	),
	'zendoauth2' => array(
		'google' => array(
			'client_id'     => '177424289670-i5va4oel3hn3vc5nn63komi5n23a8f4n.apps.googleusercontent.com',
			'client_secret' => '4ow-UR6zVFC7PF6mv9dMUP9B',
			'redirect_uri'  => 'http://oraproject.org/auth/login/google',
		),
		'linkedin' => array(
			'client_id'     => '75j6lrdoli7uyw',
			'client_secret' => 'v1lxsNlHIImx4gqI',
			'redirect_uri'  => 'http://oraproject.org/auth/login/linkedin',
		)
	)
);