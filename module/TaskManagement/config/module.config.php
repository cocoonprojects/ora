<?php
namespace TaskManagement;

return array(
	'router' => [
		'routes' => [
			'collaboration-home' => [
				'type' => 'Segment',
				'options' => [
					'route' => '/:orgId/items[/:id]',
					'constraints' => [
						'orgId' => '([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})',
						'id' => '([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})',
					]
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
					'route'	   => '[/:orgId]/task-management/tasks[/:id][/:controller][/:type]',
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
	    'TaskManagement\CloseItemIdeaListener',
	    'TaskManagement\AcceptCompletedItemListener',
		'TaskManagement\TransferCreditsListener',
		'TaskManagement\AssignCreditsListener',
	],
	'assignment_of_shares_timebox' => new \DateInterval('P10D'),
	'assignment_of_shares_remind_interval' => new \DateInterval('P7D'),
	'default_tasks_limit' => 10,
	'item_idea_voting_timebox' => new \DateInterval('P7D'),
	'completed_item_voting_timebox' => new \DateInterval('P7D'),
);
