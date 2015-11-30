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
					),
					'constraints' => array(
						'orgId' => '([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})',
					),
				),
			),
			'organizations-home' => array(
				'type' => 'Literal',
				'options' => array(
					'route'    => '/organizations-home',
					'defaults' => array(
						'controller' => 'People\Controller\Index',
						'action'	 => 'organizations',
					),
				),
			),
			'organizations' => [
				'type' => 'Segment',
				'options' => [
					'route' => '/organizations[/:id][/:controller]',
					'constraints' => [
						'id' => '([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})'
					],
					'defaults' => [
						'__NAMESPACE__' => 'People\Controller',
						'controller' => 'Organizations',
					],
				],
			],
			'members' => [
				'type' => 'Segment',
				'options' => [
					'route' => '/:orgId/people/members[/:id][/:controller]',
					'constraints' => [
						'orgId' => '([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})',
						'id'    => '([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})'
					],
					'defaults' => [
						'__NAMESPACE__' => 'People\Controller',
						'controller'    => 'Members'
					],
				],
			],
			'profiles'	=> array(
				'type' => 'Segment',
				'options' => array(
					'route'    => '/:orgId/profiles[/:id]',
					'defaults' => array(
						'controller' => 'People\Controller\Index',
						'action'	 => 'profile',
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
	),
	'default_members_limit' => 20
);
