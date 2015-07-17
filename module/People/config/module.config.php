<?php
namespace People;

return array(
	'router' => array(
		'routes' => array(
			'people-home' => array(
				'type' => 'Segment',
				'options' => array(
					'route'    => '/:orgId/people',
					'defaults' => array(
						'controller' => 'People\Controller\Index',
						'action'	 => 'index',
						'orgId' => ''
					),
					'constraints' => array(
						'orgId' => '([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})',
					),
				),
			),
			'organizations-home' => array(
				'type' => 'Literal',
				'options' => array(
					'route'    => '/organizations',
					'defaults' => array(
						'controller' => 'People\Controller\Index',
						'action'	 => 'organizations',
					),
				),
			),
				
			'organizations' => array(
				'type' => 'Segment',
				'options' => array(
					'route' => '/people/organizations[/:orgId][/:controller]',
					'constraints' => array(
						'orgId' => '([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})'
					),
					'defaults' => array(
						'__NAMESPACE__' => 'People\Controller',
						'controller' => 'Organizations'
					),
				),
			),	
			'organizations-entities' => array(
				'type' => 'Segment',
				'options' => array(
					//'route' => '/people/organizations/:orgId/:controller[/:id]',
					'route' => '/:orgId/people/:controller[/:id]',
					'constraints' => array(
						'orgId' => '([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})',
					),
					'defaults' => array(
						'__NAMESPACE__' => 'People\Controller',
						'orgId' => ''
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
			__NAMESPACE__ => __DIR__ . '/../view',
		)
	),
	'asset_manager' => array(
		'resolver_configs' => array(
			'paths' => array(
				__NAMESPACE__ => __DIR__ . '/../public',
			),
		),
	),
	'service_manager' => array(
		'invokables' => array(
			'People\Assertion\MemberOfOrganizationAssertion' => 'People\Assertion\MemberOfOrganizationAssertion',
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
		'People\OrganizationCommandsListener',
		'People\SendMailListener'
	)
);
