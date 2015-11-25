<?php
namespace Kanbanize;

return array(
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
		//'Kanbanize\SyncTaskListener',	// Actions on kanbanize tasks come directly from Kanbanize, not from O.R.A.
		'Kanbanize\KanbanizeTasksListener'
	),
	'router' => array(
		'routes' => array(
			'kanbanize-import' => array(
				'type' => 'Segment',
				'options' => array(
					'route'    => '/:orgId/kanbanize/imports',
					'defaults' => array(
						'__NAMESPACE__' => 'Kanbanize\Controller',
						'controller' => 'Imports',
					),
					'constraints' => array(
						'orgId' => '([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})',
					),
				),
			)
		)
	)
);