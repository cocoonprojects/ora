<?php

return array(

	'controllers' => array(
        'invokables' => array(
            'TaskManagement\Controller\Task' => 'TaskManagement\Controller\TaskController',
        ),
    ),
    
    'router' => array(
        'routes' => array(
            'task' => array(
                'type' => 'Segment',
                'options' => array(
                    'route'    => '/task-management/task[/:id]',
                    'constraints' => array(
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'TaskManagement\Controller\Task'
                    ),
                ),
            ),
            'projects' => array(
                'type' => 'Segment',
                'options' => array(
                    'route'    => '/task-management/projects[/:id]',
                    'constraints' => array(                         
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'TaskManagement\Controller\Projects'
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
