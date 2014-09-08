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
                     'route'    => '/tasks-management/projects/:idproject/tasks[/:idtask]',
                     'constraints' => array(                         
                         'idtask'     => '[0-9]+',
     					 'idproject'     => '[0-9]+',
                     ),
                     'defaults' => array(
                         'controller' => 'TasksManagement\Controller\Tasks'
                     ),            
                ),
            ),
            'projects' => array(
                'type' => 'Segment',
                'options' => array(
                     'route'    => '/tasks-management/projects[/:idproject]',
                     'constraints' => array(                         
                         'idproject'     => '[0-9]+',
                     ),
                     'defaults' => array(
                         'controller' => 'TasksManagement\Controller\Projects'
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
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
            ),
        ),
    ),
);
