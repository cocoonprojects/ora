<?php
namespace Application;

return array(
	'router' => array(
		'routes' => array(
			'memberships' => array(
				'type' => 'Segment',
				'options' => array(
					'route'	   => '/memberships',
					'constraints' => array(
						'id' => '[0-9a-z\-]+'
					),
					'defaults' => array(
						'__NAMESPACE__' => 'Application\Controller',
						'controller' => 'Memberships'
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
		'driver' => array(
			 __NAMESPACE__ . '_driver' => array(
				'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
				'cache' => 'array',
				'paths' => array(__DIR__ . '/../src/'. __NAMESPACE__ . '/Entity')
			),
			'orm_default' => array(
				'drivers' => array(
					__NAMESPACE__ . '\Entity' =>  __NAMESPACE__ . '_driver'
				)
			)
		)
	),
	'listeners' => array(
		'Application\LoadLocalProfileListener',
		'Application\DomainEventDispatcher'
	),
);
