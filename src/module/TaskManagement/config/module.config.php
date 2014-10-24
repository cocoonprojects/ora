<?php

return array(
	'service_manager' => array(
		'factories' => array(
			'TaskManagement\Service\Kanbanize' => 'TaskManagement\Service\KanbanizeServiceFactory'
		),
	),
	'controllers' => array(
        'invokables' => array(
            'TaskManagement\Controller\Members' => 'TaskManagement\Controller\MembersController',
            'TaskManagement\Controller\Tasks' => 'TaskManagement\Controller\TasksController',
            'TaskManagement\Controller\TaskTransitions' => 'TaskManagement\Controller\TaskTransitionsController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'members' => array(
                'type' => 'Segment',
                'options' => array(
                    'route'    => '/task-management/tasks/:taskid/members/[:id]',
                    'constraints' => array(
                        'taskid' => '[a-zA-Z0-9]+',
                        'id' => '[a-zA-Z0-9]+'
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
                        'id' => '[a-zA-Z0-9]+'
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
        			'constraints' => array(
        				'id' => '[0-9]+',
        			),
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
    'translator' => array(),
    'view_manager' => array(
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    )
);
