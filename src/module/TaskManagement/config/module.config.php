<?php

return array(

	'controllers' => array(
        'invokables' => array(
            'TaskManagement\Controller\Members' => 'TaskManagement\Controller\MembersController',
            'TaskManagement\Controller\Tasks' => 'TaskManagement\Controller\TasksController'
        ),
    ),
    
    'router' => array(
        'routes' => array(
            'members' => array(
                'type' => 'Segment',
                'options' => array(
                    'route'    => '/task-management/tasks/:taskid/members/:id',
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
