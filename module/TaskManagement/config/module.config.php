<?php
namespace TaskManagement;

return array(
	'router' => [
		'routes' => [
			'collaboration-home' => [
				'type' => 'Segment',
				'options' => [
					'route' => '/:orgId/task-management',
					'constraints' => [
						'orgId' => '([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})',
					],
					'defaults' => [
						'controller' => 'TaskManagement\Controller\Index',
						'action' => 'index',
					],
				],
			],
			'collaboration' => [
				'type' => 'Segment',
				'options' => [
					'route'	   => '/:orgId/task-management/:controller[/:id]',
					'constraints' => [
						'id' => '([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})',
						'orgId' => '([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})',
					],
					'defaults' => [
						'__NAMESPACE__' => 'TaskManagement\Controller',
						'controller' => 'Index',
					],
				],
			],
			'tasks' => [
				'type' => 'Segment',
				'options' => [
					'route'	   => '/:orgId/task-management/tasks[/:id][/:controller][/:type]',
					'constraints' => [
						'orgId' => '([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})',
						'id' => '([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})',
						'type' => '[a-zA-Z-]+'
					],
					'defaults' => [
						'__NAMESPACE__' => 'TaskManagement\Controller',
						'controller' => 'Tasks'
					],
				],
			],
		],
	],
	'translator' => array(),
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
	'listeners' => [
		'TaskManagement\NotifyMailListener',
		'TaskManagement\StreamCommandsListener',
		'TaskManagement\TaskCommandsListener',
		'TaskManagement\TransferCreditsListener',
		'TaskManagement\CloseTaskListener',
		'TaskManagement\AssignCreditsListener',
	],
	'assignment_of_shares_timebox' => new \DateInterval('P10D'),
	'assignment_of_shares_remind_interval' => new \DateInterval('P7D'),
	'default_tasks_limit' => 10
);
