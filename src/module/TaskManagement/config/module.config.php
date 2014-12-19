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
                    'route'    => '/task-management/',
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
                    'route'    => '/task-management/tasks[/:id]',
                    'constraints' => array(
                        'id' => '[0-9a-z\-]+'
                    ),
                    'defaults' => array(
                        'controller' => 'TaskManagement\Controller\Tasks'
                    ),
                ),
            ),
            'members' => array(
                'type' => 'Segment',
                'options' => array(
                    'route'    => '/task-management/tasks/:taskId/members',
                    'constraints' => array(
                        'taskId' => '[0-9a-z\-]+'
                    ),
                    'defaults' => array(
                        'controller' => 'TaskManagement\Controller\Members'
                    ),
                ),
            ),
            'transitions' => array(
        		'type'    => 'Segment',
        		'options' => array(
        			// Change this to something specific to your module
        			'route'    => '/task-management/tasks/:id/transitions',
        			'defaults' => array(
        				// Change this value to reflect the namespace in which
        				// the controllers for your module are found
        				'__NAMESPACE__' => 'TaskManagement\Controller',
        				'controller'    => 'Transitions',
        			),
        		),
        	),
        	'estimations'	=> array(
        		'type'		=> 'Segment',
        		'options'	=> array(
        			'route'		=> '/task-management/tasks/:taskId/estimation',
        			'defaults'	=>	array(
        				'__NAMESPACE__' => 'TaskManagement\Controller',
        				'controller'    => 'Estimation',
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
    )
);