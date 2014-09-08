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
			'accounts' => array (
				'type'    => 'segment',
				'options' => array (
					'route'       => '/accounting/accounts[/:id]',
					'constraints' => array (
						'id'     => '[0-9]+',
					),
					'defaults'    => array (
						'controller' => 'Accounting\Controller\Accounts'
					),
				),
			),
			'home' => array(
					'type'    => 'segment',
					'options' => array(
							'route'    => '/accounting[/][:action][/:id]',
							'constraints' => array(
									'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
									'id'     => '[0-9]+',
							),
							'defaults' => array(
									'controller' => 'Accounting\Controller\Index',
									'action'     => 'index',
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