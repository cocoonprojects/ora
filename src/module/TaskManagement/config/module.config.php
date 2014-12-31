<?php
return array(
	'service_manager' => array(
		'factories' => array(
			'TaskManagement\Service\Kanbanize' => 'TaskManagement\Service\KanbanizeServiceFactory'
		),
	),
	'router' => array(
        'routes' => array(
            'tasks-home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/task-management',
                    'defaults' => array(
                        'controller' => 'TaskManagement\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),
        	'streams' => array(
				'type' => 'Segment',
				'options' => array(
					'route'    => '/task-management/streams[/:id]',
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
                    'route'    => '/task-management/tasks[/:id][/:controller]',
                    'constraints' => array(
                        'id' => '[0-9a-z\-]+'
                    ),
                    'defaults' => array(
        				'__NAMESPACE__' => 'TaskManagement\Controller',
                    	'controller' => 'Tasks'
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
			'task-management' => __DIR__ . '/../view',
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