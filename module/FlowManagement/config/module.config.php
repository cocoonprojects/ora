<?php
namespace FlowManagement;

return array(
		'router' => array(
				'routes' => array(
						'flow' => [
								'type'	  => 'literal',
								'options' => [
										'route'		=> '/flow-management/cards',
										'defaults'	=> [
												'__NAMESPACE__' => 'FlowManagement\Controller',
												'controller'	=> 'Cards',
										]
								]
						],
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
		'listeners' => [
			'FlowManagement\CardCommandsListener',
			'FlowManagement\ItemCommandsListener',
		],
);