<?php
return array(
	'router' => array(
        'routes' => array(
           'login' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/auth/login[/:id]',
                    'defaults' => array(
                        'controller'    => 'Auth\Controller\Login'
                    ),
                ),
            ),

            'logout' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/auth/logout',
                    'defaults' => array(
                        'controller'    => 'Auth\Controller\Logout',
                    ),
                ),
            ),
		),	
    ),

    'view_manager' => array(
        'strategies' => array(
            'ViewJsonStrategy',
        ),

    	'template_path_stack' => array(
    			'auth' => __DIR__ . '/../view',
    	),    		
    ),
);