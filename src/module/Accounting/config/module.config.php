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
			'deposits' => array (
				'type'    => 'segment',
				'options' => array (
					'route'       => '/accounting/accounts/:accountId/deposits',
					'constraints' => array (
						'accountId'     => '[0-9a-z\-]+',
					),
					'defaults'    => array (
						'controller' => 'Accounting\Controller\Deposits'
					),
				),
			),
			'statements' => array (
				'type'    => 'segment',
				'options' => array (
					'route'       => '/accounting/accounts/:id/statement',
					'constraints' => array (
						'id'     => '[0-9a-z\-]+',
					),
					'defaults'    => array (
						'controller' => 'Accounting\Controller\Statements'
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
	),
	'asset_manager' => array(
			'resolver_configs' => array(
					'paths' => array(
							'Application' => __DIR__ . '/../public',
					),
			),
	),
);