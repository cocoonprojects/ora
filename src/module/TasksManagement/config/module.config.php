<?php

return array(

	'controllers' => array(
         'invokables' => array(
             'TasksManagement\Controller\Tasks' => 'TasksManagement\Controller\TasksController',
         ),
     ),

    'router' => array(
        'routes' => array(
            'tasks' => array(
                'type' => 'Segment',
                'options' => array(
                     'route'    => '/tasks[/:id]',
                     'constraints' => array(                         
                         'id'     => '[0-9]+',
                     ),
                     'defaults' => array(
                         'controller' => 'TasksManagement\Controller\Tasks',
                         'action'     => 'index',
                     ),            
                ),
            ),
            'projects' => array(
                'type' => 'Segment',
                'options' => array(
                     'route'    => '/tasks[/:id]',
                     'constraints' => array(                         
                         'id'     => '[0-9]+',
                     ),
                     'defaults' => array(
                         'controller' => 'TasksManagement\Controller\Tasks',
                         'action'     => 'index',
                     ),            
                ),
            ),
        ),
    ),
    
    'service_manager' => array(),
    'translator' => array(),
    'view_manager' => array(),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
            ),
        ),
    ),
);
