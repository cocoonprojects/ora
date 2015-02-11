<?php

return array(
	'service_manager' => array(
		'factories' => array(
			'TaskManagement\Service\Kanbanize' => 'TaskManagement\Service\KanbanizeServiceFactory'
		),
	),
	'router' => array(
        'routes' => array(
            'tasks-home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/task-management',
                    'defaults' => array(
                        'controller' => 'TaskManagement\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),
        	'streams' => array(
				'type' => 'Segment',
				'options' => array(
					'route'    => '/task-management/streams[/:id]',
					'constraints' => array(
						'id' => '[0-9a-z\-]+',
					),
					'defaults' => array(
						'controller' => 'TaskManagement\Controller\Streams'
					),
				),
			),
            'tasks' => array(
                'type' => 'Segment',
                'options' => array(
                    'route'    => '/task-management/tasks[/:id][/:controller]',
                    'constraints' => array(
                        'id' => '[0-9a-z\-]+'
                    ),
                    'defaults' => array(
        				'__NAMESPACE__' => 'TaskManagement\Controller',
                    	'controller' => 'Tasks'
                    ),
                ),
            ),
        ),
    ),
    
    'translator' => array(),
	'view_manager' => array(
		'strategies' => array(
			'ViewJsonStrategy',
		),
		'template_path_stack' => array(
			'task-management' => __DIR__ . '/../view',
		),
	),
	'asset_manager' => array(
		'resolver_configs' => array(
			'paths' => array(
				'TaskManagement' => __DIR__ . '/../public',
			),
		),
    ),
    
    'bjyauthorize'=> array(
    
    	'resource_providers' => array(
            'BjyAuthorize\Provider\Resource\Config' => array(
                'Ora\StreamManagement\Stream' => array(),
            ),
        ),
    
        'rule_providers' => array(
            'BjyAuthorize\Provider\Rule\Config' => array(
                'allow' => array(
                    
<<<<<<< HEAD
                    array(array('user'), 
                    		'Ora\StreamManagement\Stream', 
                    		array('createTask'), 
                    		'assertion.CreateTaskAssertion'),
                                        
=======
                    array(
                    	array('user'), 
                    	'Ora\StreamManagement\Stream', 
                    	array('createTask'), 
                    	'assertion.CreateTaskAssertion'),                    	
                    array(
                    	array('user'), 
                    	'DoctrineORMModule\Proxy\__CG__\Ora\ReadModel\Stream', 
                    	array('createTask'), 
                    	'assertion.CreateTaskAssertion'),
					array(
                    	array('user'), 
                    	'Ora\ReadModel\Task', 
                    	array('joinTask'), 
                    	'assertion.JoinTaskAssertion'),
                    array(
                    	array('user'), 
                    	'Ora\TaskManagement\Task', 
                    	array('estimateTask'), 
                    	'assertion.EstimateTaskAssertion'),
                    array(
                    	array('user'), 
                    	'Ora\ReadModel\Task', 
                    	array('estimateTask'), 
                    	'assertion.EstimateTaskAssertion'),
                    array(
                    	array('user'), 
                    	'Ora\ReadModel\Task', 
                    	array('unjoinTask'), 
                    	'assertion.UnjoinTaskAssertion'),
                    array(
                    	array('user'), 
                    	'Ora\TaskManagement\Task', 
                    	array('unjoinTask'), 
                    	'assertion.UnjoinTaskAssertion'),
                    array(
                    	array('user'), 
                    	'Ora\ReadModel\Task', 
                    	array('deleteTask'), 
                    	'assertion.DeleteTaskAssertion'),
                    array(
                    	array('user'), 
                    	'Ora\TaskManagement\Task', 
                    	array('deleteTask'), 
                    	'assertion.DeleteTaskAssertion'),
>>>>>>> adding acl assertions on task management
                ),
            ),
        ),
    ),
);
