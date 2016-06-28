<?php
namespace TaskManagement;

return array(
	'router' => [
		'routes' => [
			'collaboration-home' => [
				'type' => 'Segment',
				'options' => [
					'route' => '/:orgId/items[/:id]',
					'constraints' => [
						'orgId' => '([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})',
						'id' => '([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})',
					]
				],
			],
			'collaboration' => [
				'type' => 'Segment',
				'options' => [
					'route'	   => '/:orgId/task-management/:controller[/:id]',
					'constraints' => [
						'id' => '([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})',
						'orgId' => '([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})',
					],
					'defaults' => [
						'__NAMESPACE__' => 'TaskManagement\Controller',
						'controller' => 'Index',
					],
				],
			],
			'tasks' => [
				'type' => 'Segment',
				'options' => [
					'route'	   => '[/:orgId]/task-management/tasks[/:id][/:controller][/:type]',
					'constraints' => [
						'orgId' => '([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})',
						'id' => '([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})',
						'type' => '[a-zA-Z-]+'
					],
					'defaults' => [
						'__NAMESPACE__' => 'TaskManagement\Controller',
						'controller' => 'Tasks'
					],
				],
			],
		],
	],
    'console' => [
        'router' => [
            'routes' => [
                'reminder' => [
                    'options' => [
                        'route'    => 'reminder [--verbose|-v]',
                        'defaults' => [
							'controller' => 'TaskManagement\Controller\Console\Reminders',
							'action' => 'send'
                        ]
                    ]
                ],
                'close_polls' => [
                    'options' => [
                        'route'    => 'closepolls [idea-items|completed-items]:type [--verbose|-v]',
                        'defaults' => [
							'controller' => 'TaskManagement\Controller\Console\VotingResults',
							'action' => 'closePolls'
                        ]
                    ]
                ]
            ]
        ]
    ],
	'translator' => array(),
	'view_manager' => array(
		'strategies' => array(
			'ViewJsonStrategy',
		),
		'template_path_stack' => array(
			__NAMESPACE__ => __DIR__ . '/../view',
		)
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
		'TaskManagement\NotifyMailListener',
		'TaskManagement\StreamCommandsListener',
		'TaskManagement\TaskCommandsListener',
	    'TaskManagement\CloseItemIdeaListener',
	    'TaskManagement\AcceptCompletedItemListener',
		'TaskManagement\TransferCreditsListener',
		'TaskManagement\AssignCreditsListener',
	],
);
