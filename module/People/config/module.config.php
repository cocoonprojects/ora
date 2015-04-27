<?php
namespace People;

return array(
	'router' => array(
		'routes' => array(
			'organizations' => array(
				'type' => 'Segment',
				'options' => array(
					'route'	   => '/people/organizations[/:id][/:controller]',
					'constraints' => array(
						'id' => '[0-9a-z\-]+'
					),
					'defaults' => array(
						'__NAMESPACE__' => 'People\Controller',
						'controller' => 'Organizations'
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
			__NAMESPACE__ => __DIR__ . '/../view',
		),
	),
	'asset_manager' => array(
		'resolver_configs' => array(
			'paths' => array(
				__NAMESPACE__ => __DIR__ . '/../public',
			),
		),
	),
// 	'doctrine' => array(
// 		'driver' => array(
// 			 __NAMESPACE__ . '_driver' => array(
// 				'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
// 				'cache' => 'array',
// 				'paths' => array(__DIR__ . '/../src/'. __NAMESPACE__ . '/Entity')
// 			),
// 			'orm_default' => array(
// 				'drivers' => array(
// 					__NAMESPACE__ . '\Entity' =>  __NAMESPACE__ . '_driver'
// 				)
// 			)
// 		)
// 	),
// 	'listeners' => array(
// 		'Application\OrganizationCommandsListener'
// 	),	
);
