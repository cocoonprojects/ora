<?php

return array(

	'controllers' => array(
        'invokables' => array(
            'TaskManagement\Controller\Members' => 'TaskManagement\Controller\MembersController',
            'TaskManagement\Controller\Tasks' => 'TaskManagement\Controller\TasksController',
            'TaskManagement\Controller\Projects' => 'TaskManagement\Controller\ProjectsController',
        ),
    ),
    
    'router' => array(
        'routes' => array(
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
