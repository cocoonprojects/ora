<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Kanbanize\Controller\KanbanizeAction' => 'Kanbanize\Controller\KanbanizeActionController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'kanbanize' => array(
                'type'    => 'Segment',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/kanbanize/task/:id',
                	'constraints' => array(
                		'id' => '[0-9]+',
                	),
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Kanbanize\Controller',
                        'controller'    => 'Kanbanize',
                    ),
                ),
                'may_terminate' => false,
                'child_routes' => array(
                    // This route is a sane default when developing a module;
                    // as you solidify the routes for your module, however,
                    // you may want to remove it and replace it with more
                     // specific routes.
                    'client' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/client/test',
                            'defaults' => array(
                                'controller' => 'KanbanizeAction',
                                'action'     => 'index',
                            ),
                        ),
                    ),
                ),
            ),'list' => array(
                'type'    => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/kanbanize/list',
                    'defaults' => array(
                    		'__NAMESPACE__' => 'Kanbanize\Controller',
                    		'controller' => 'KanbanizeAction',
                    		'action'     => 'list',
                    ),
                ),
                'may_terminate' => true
            )
        ),
    ),

		'view_manager' => array(
				'display_not_found_reason' => true,
				'display_exceptions'       => true,
				'doctype'                  => 'HTML5',
// 				'template_map' => array(
// 						'kanbanize/kanbanize-action/index' => __DIR__ . '/../view/kanbanize/kanbanize-action/index.phtml',
// 				),
				'template_path_stack' => array(
						__DIR__ . '/../view',
				),
		),

);
