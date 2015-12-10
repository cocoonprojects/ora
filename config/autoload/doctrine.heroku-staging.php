<?php
return [
	'doctrine' => [
		'connection' => [
			'orm_default' => [
				'driverClass' => 'Doctrine\DBAL\Driver\PDOPgSql\Driver',
				'params' => [ 'url' => getenv('DATABASE_URL') ]
			]
		]
	]
];