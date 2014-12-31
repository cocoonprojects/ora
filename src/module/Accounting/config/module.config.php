<?php
return array(
	'router' => array(
		'routes' => array(
            'accounting-home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/accounting/',
                    'defaults' => array(
                        'controller' => 'Accounting\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),
			'accounts' => array (
				'type'    => 'segment',
				'options' => array (
					'route'       => '/accounting/accounts[/:id][/:controller]',
					'constraints' => array (
						'id'     => '[0-9a-z\-]+',
					),
					'defaults'    => array (
						'__NAMESPACE__' => 'Accounting\Controller',
						'controller' => 'Accounts'
					),
				),
			),
		),
	),
	'view_manager' => array(
		'strategies' => array(
			'ViewJsonStrategy',
		),
		'template_path_stack' => array(
			'accounting' => __DIR__ . '/../view',
		),
	),
	'asset_manager' => array(
		'resolver_configs' => array(
			'paths' => array(
				'Application' => __DIR__ . '/../public',
			),
		),
	),
);