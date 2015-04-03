<?php
return array(
	'router' => array(
		'routes' => array(
			'home' => array(
				'type' => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
					'route'	   => '/',
					'defaults' => array(
						'controller' => 'Application\Controller\Index',
						'action'	 => 'index',
					),
				),
			),
			'login' => array(
				'type' => 'Zend\Mvc\Router\Http\Segment',
				'options' => array(
						'route'	   => '/auth/:action[/:id]',
						'defaults' => array(
							'controller'	=> 'Application\Controller\Auth',
						),
				),
			),
			'organizations' => array(
				'type' => 'Segment',
				'options' => array(
					'route'	   => '/organizations[/:id][/:controller]',
					'constraints' => array(
						'id' => '[0-9a-z\-]+'
					),
					'defaults' => array(
						'__NAMESPACE__' => 'Application\Controller',
						'controller' => 'Organizations'
					),
				),
			),	
			// The following is a route to simplify getting started creating
			// new controllers and actions without needing to create a new
			// module. Simply drop new controllers in, and you can access them
			// using the path /application/:controller/:action
			'application' => array(
				'type'	  => 'Literal',
				'options' => array(
					'route'	   => '/application',
					'defaults' => array(
						'__NAMESPACE__' => 'Application\Controller',
						'controller'	=> 'Index',
						'action'		=> 'index',
					),
				),
				'may_terminate' => true,
				'child_routes' => array(
					'default' => array(
						'type'	  => 'Segment',
						'options' => array(
							'route'	   => '/[:controller[/:action]]',
							'constraints' => array(
								'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
								'action'	 => '[a-zA-Z][a-zA-Z0-9_-]*',
							),
							'defaults' => array(
							),
						),
					),
				),
			),
		),
	),
	'service_manager' => array(
		'abstract_factories' => array(
			'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
			'Zend\Log\LoggerAbstractServiceFactory',
		),
		'aliases' => array(
			'translator' => 'MvcTranslator',
		)
	),
	'translator' => array(
		'locale' => 'en_US',
		'translation_file_patterns' => array(
			array(
				'type'	   => 'gettext',
				'base_dir' => __DIR__ . '/../language',
				'pattern'  => '%s.mo',
			),
		),
	),
	'view_manager' => array(
		'display_not_found_reason' => true,
		'display_exceptions'	   => true,
		'doctype'				   => 'HTML5',
		'not_found_template'	   => 'error/404',
		'exception_template'	   => 'error/index',
		'template_map' => array(
			'layout/layout'			  => __DIR__ . '/../view/layout/layout.phtml',
			'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
			'error/404'				  => __DIR__ . '/../view/error/404.phtml',
			'error/index'			  => __DIR__ . '/../view/error/index.phtml',
		),
		'template_path_stack' => array(
			__DIR__ . '/../view',
		),
	),
	
	// Placeholder for console routes
	'console' => array(
		'router' => array(
			'routes' => array(
			),
		),
	),
	'doctrine' => array(
				 
		'configuration' => array(
			'orm_default' => array(
				'generate_proxies'	=> true,
				'proxy_dir'			=> __DIR__ . '/../../../data/DoctrineORMModule/Proxies/'
			)
		),
	
		'driver' => array(
			 __NAMESPACE__ . '_driver' => array(
				'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
				'cache' => 'array',
				'paths' => array(__DIR__ . '/../../../library/Ora')
			),
			'orm_default' => array(
				'drivers' => array(
					'Ora' =>  __NAMESPACE__ . '_driver'
				)
			)
		)
	),
	'asset_manager' => array(
		'resolver_configs' => array(
			'paths' => array(
				'Application' => __DIR__ . '/../public',
			),
		),
	),
	'listeners' => array(
		'Application\OrganizationCommandsListener'
	),	
);
