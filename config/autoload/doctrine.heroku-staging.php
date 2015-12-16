<?php
return [
    'doctrine' => [
        'configuration' => [
            'orm_default' => [
                'generate_proxies' => true,
                'proxy_dir'        => __DIR__ . '/../../data/DoctrineORMModule/Proxies/'
            ]
        ],
		'connection' => [
			'orm_default' => [
				'driverClass' => 'Doctrine\DBAL\Driver\PDOPgSql\Driver',
				'params' => [ 'url' => getenv('DATABASE_URL') ]
			]
		]
	]
];
