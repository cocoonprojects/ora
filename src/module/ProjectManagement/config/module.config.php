<?php

return array(
	'controllers' => array(
        'invokables' => array(
            'ProjectManagement\Controller\Projects' => 'ProjectManagement\Controller\ProjectsController',
        ),
    ),
    
    'router' => array(
        'routes' => array(
            'project' => array(
                'type' => 'Segment',
                'options' => array(
                    'route'    => '/project-management/project[/:id]',
                    'constraints' => array(
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'ProjectManagement\Controller\Projects'
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