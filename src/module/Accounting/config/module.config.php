<?php
return array(
	'controllers' => array(
		'invokables' => array(
			'Accounting\Controller\Accounts' => 'Accounting\Controller\AccountsController',
			'Accounting\Controller\Index' => 'Accounting\Controller\IndexController',
		),
	),
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
					'route'       => '/accounting/accounts[/:id]',
					'constraints' => array (
						'id'     => '[0-9a-z\-]+',
					),
					'defaults'    => array (
						'controller' => 'Accounting\Controller\Accounts'
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
		'template_map' => array(
			'accounting/layout' => __DIR__ . '/../view/layout/layout.phtml',
		),
	),
);