<?php
namespace TaskManagement;

return array(
	'router' => array(
		'routes' => array(
			'tasks-home' => array(
				'type' => 'Segment',
				'options' => array(
					'route'	   => '/:orgId/task-management',
					'constraints' => array(
						'orgId' => '([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})',
					),
					'defaults' => array(
						'controller' => 'TaskManagement\Controller\Index',
						'action'	 => 'index',
					),
				),
			),
			'streams' => array(
				'type' => 'Segment',
				'options' => array(
					'route'	   => '/:orgId/task-management/streams[/:id]',
					'constraints' => array(
						'id' => '[0-9a-z\-]+',
						'orgId' => '([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})',
					),
					'defaults' => array(
						'controller' => 'TaskManagement\Controller\Streams',
					),
				),
			),
			'tasks' => array(
				'type' => 'Segment',
				'options' => array(				
					'route'	   => '/:orgId/task-management/tasks[/:id][/:controller]',
					'constraints' => array(
						'orgId' => '([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})',
						'id' => '([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})',
					),
					'defaults' => array(
						'__NAMESPACE__' => 'TaskManagement\Controller',
						'controller' => 'Tasks',
					),
				),
			),
			'task-reminders' => array(
				'type' => 'Segment',
				'options' => array(
					'route'	   => '/task-management/tasks/reminders/:id',
					'constraints' => array(
						'id' => '[a-zA-Z-]+',
					),
					'defaults' => array(
						'__NAMESPACE__' => 'TaskManagement\Controller',
						'controller' => 'Reminders'
					),
				),
			),
			'task-transitions' => array(
				'type' => 'Segment',
				'options' => array(
						'route'	   => '/task-management/tasks/transitions',
						'defaults' => array(
								'__NAMESPACE__' => 'TaskManagement\Controller',
								'controller' => 'Transitions'
						),
				),
			)
		),
	),
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
	'listeners' => array(
		'TaskManagement\NotifyMailListener',	
		'TaskManagement\StreamCommandsListener',

		'TaskManagement\TaskCommandsListener',
		'TaskManagement\TransferTaskSharesCreditsListener',
		'TaskManagement\CloseTaskListener',
	),
	'assignment_of_shares_timebox' => new \DateInterval('P7D'), 
);
