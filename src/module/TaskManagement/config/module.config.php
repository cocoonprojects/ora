<?php

return array(

	'controllers' => array(
        'invokables' => array(
            'TaskManagement\Controller\Tasks' => 'TaskManagement\Controller\TasksController',
        ),
    ),
    
    'router' => array(
        'routes' => array(
            'task' => array(
                'type' => 'Segment',
                'options' => array(
                    'route'    => '/task-management/task[/:id]',
                    'constraints' => array(
                        'id' => '[a-zA-Z0-9]+',
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
