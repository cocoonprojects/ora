<?php
namespace TaskManagement;

return array(
	'router' => array(
		'routes' => array(
			'tasks-home' => array(
				'type' => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
					'route'	   => '/task-management',
					'defaults' => array(
						'controller' => 'TaskManagement\Controller\Index',
						'action'	 => 'index',
					),
				),
			),
			'streams' => array(
				'type' => 'Segment',
				'options' => array(
					'route'	   => '/task-management/streams[/:id]',
					'constraints' => array(
						'id' => '[0-9a-z\-]+',
					),
					'defaults' => array(
						'controller' => 'TaskManagement\Controller\Streams'
					),
				),
			),
			'tasks' => array(
				'type' => 'Segment',
				'options' => array(
					'route'	   => '/task-management/tasks[/:id][/:controller]',
					'constraints' => array(
						'id' => '[0-9a-z\-]+'
					),
					'defaults' => array(
						'__NAMESPACE__' => 'TaskManagement\Controller',
						'controller' => 'Tasks'
					),
				),
			),
			'timebox-shares' => array(
				'type' => 'Literal',
				'options' => array(
					'route'	   => '/task-management/tasks/applytimeboxforshares',
					
					'defaults' => array(
						'controller' => 'TaskManagement\Controller\Tasks',
						'action' => 'applytimeboxforshares'
					),
				),
			),
		),
	),
	'translator' => array(),
	'view_manager' => array(
		'strategies' => array(
			'ViewJsonStrategy',
		),
		'template_path_stack' => array(
			__NAMESPACE__ => __DIR__ . '/../view',
		),
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
		'TaskManagement\StreamCommandsListener',
		'TaskManagement\TaskCommandsListener',
		'TaskManagement\TransferTaskSharesCreditsListener',
		'TaskManagement\CloseTaskListener',
	),
);
