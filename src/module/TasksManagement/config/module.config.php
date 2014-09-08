<?php

return array(

	'controllers' => array(
         'invokables' => array(
             	'TasksManagement\Controller\Tasks' => 'TasksManagement\Controller\TasksController',
				'TasksManagement\Controller\Projects' => 'TasksManagement\Controller\ProjectsController',
         ),
     ),

    'router' => array(
        'routes' => array(
            'tasks' => array(
                'type' => 'Segment',
                'options' => array(
                     'route'    => '/tasks-management/projects/:projectId/tasks[/:id]',
                     'constraints' => array(                         
                         'id'     => '[0-9]+',
     					 'projectId'     => '[0-9]+',
                     ),
                     'defaults' => array(
                         'controller' => 'TasksManagement\Controller\Tasks'
                     ),            
                ),
            ),
            'projects' => array(
                'type' => 'Segment',
                'options' => array(
                     'route'    => '/tasks-management/projects[/:id]',
                     'constraints' => array(                         
                         'id'     => '[0-9]+',
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
    )
);
