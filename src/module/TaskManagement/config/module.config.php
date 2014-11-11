<?php
return array(
	'service_manager' => array(
		'factories' => array(
			'TaskManagement\Service\Kanbanize' => 'TaskManagement\Service\KanbanizeServiceFactory'
		),
	),
	'controllers' => array(
        'invokables' => array(
            'TaskManagement\Controller\TaskTransitions' => 'TaskManagement\Controller\TaskTransitionsController',
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
        	'projects' => array(
				'type' => 'Segment',
				'options' => array(
					'route'    => '/task-management/projects[/:id]',
					'constraints' => array(
						'id' => '[0-9a-z\-]+',
					),
					'defaults' => array(
						'controller' => 'TaskManagement\Controller\Projects'
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
            'transitions' => array(
        		'type'    => 'Segment',
        		'options' => array(
        			// Change this to something specific to your module
        			'route'    => '/task-management/tasks/:id/transitions',
        			'defaults' => array(
        				// Change this value to reflect the namespace in which
        				// the controllers for your module are found
        				'__NAMESPACE__' => 'TaskManagement\Controller',
        				'controller'    => 'TaskTransitions',
        			),
        		),
        	),
        ),
    ),
    
    'service_manager' => array(),
    'translator' => array(),
    'view_manager' => array(
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    )
);