<?php

return array(
	'controllers' => array(
        'invokables' => array(
            'User\Controller\Users' => 'User\Controller\UsersController'
        ),
    ),
    
    'router' => array(
        'routes' => array(
            'users' => array(
                'type' => 'Segment',
                'options' => array(
                    'route'    => '/user/users[/:id]',
                    'constraints' => array(
                        'id' => '[a-zA-Z0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'User\Controller\Users'
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