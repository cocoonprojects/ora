<?php
namespace Accounting;

return array(
	'router' => array(
		'routes' => array(
			'accounts' => [
				'type'	  => 'segment',
				'options' => [
					'route'       => '/:orgId/accounting/accounts[/:id][/:controller]',
					'constraints' => [
						'id'    => '([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})',
						'orgId' => '([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})',
					],
					'defaults'	  => [
						'__NAMESPACE__' => 'Accounting\Controller',
						'controller'    => 'Accounts',
					]
				]
			],
			'statements' => [
				'type'    => 'Segment',
				'options' => [
					'route'       => '/:orgId/accounting/:controller[/:id]',
					'constraints' => [
						'orgId'      => '([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})',
						'controller' => 'personal-statement|organization-statement|members'
					],
					'defaults'    => [
						'__NAMESPACE__' => 'Accounting\Controller'
					]
				]
			]
		),
	),
	'view_manager' => array(
		'strategies' => array(
			'ViewJsonStrategy',
		),
		'template_path_stack' => array(
			__NAMESPACE__ => __DIR__ . '/../view',
		),
	),
	'doctrine' => array(
		'driver' => array(
			 __NAMESPACE__ . '_driver' => array(
			 		'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
			 		'cache' => 'array',
			 		'paths' => array(__DIR__ . '/../src/'. __NAMESPACE__ . '/Entity')
			 ),
			'orm_default' => array(
				'drivers' => array(
					__NAMESPACE__ . '\Entity' =>  __NAMESPACE__ . '_driver'
				)
			)
		)
	),	
	'listeners' => array(
		'Accounting\AccountCommandsListener',
		'Accounting\CreatePersonalAccountListener',
		'Accounting\CreateOrganizationAccountListener'
	),
	'personal_transactions_default_limit' => 10,
	'organization_transactions_default_limit' => 10
);