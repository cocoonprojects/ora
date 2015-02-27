<?php

return array(
	
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
				'Ora\ReadModel\Task' => array(),
    			'DoctrineORMModule\Proxy\__CG__\Ora\ReadModel\Stream' => array(),
            ),
        ),
    
        'rule_providers' => array(
            'BjyAuthorize\Provider\Rule\Config' => array(
                'allow' => array(
                                        	
                    array(
                    	array('user'), 
                    	'DoctrineORMModule\Proxy\__CG__\Ora\ReadModel\Stream', 
                    	array('TaskManagement.Task.create'), 
                    	'MemberOfOrganizationAssertion'),
					array(
                    	array('user'), 
                    	'Ora\ReadModel\Task', 
                    	array('TaskManagement.Task.join'), 
                    	'OrganizationMemberNotTaskMemberAndNotCompletedTaskAssertion'),                    
                    array(
                    	array('user'), 
                    	'Ora\ReadModel\Task', 
                    	array('TaskManagement.Task.estimate'), 
                    	'TaskMemberAndOngoingTaskAssertion'),
                    array(
                    	array('user'), 
                    	'Ora\ReadModel\Task', 
                    	array('TaskManagement.Task.unjoin'), 
                    	'TaskMemberNotOwnerAndNotCompletedTaskAssertion'),                    
                    array(
                    	array('user'), 
                    	'Ora\ReadModel\Task', 
                    	array('TaskManagement.Task.edit', 'TaskManagement.Task.delete'), 
                    	 'TaskOwnerAndNotCompletedTaskAssertion'),                    
					array(
                    	array('user'), 
                    	'Ora\ReadModel\Task', 
                    	array('TaskManagement.Task.execute'), 
                    	'TaskMemberNotOwnerAndOpenOrCompletedTaskAssertion'),                    
                   array(
                    	array('user'), 
                    	'Ora\ReadModel\Task', 
                    	array('TaskManagement.Task.complete'), 
                    	'TaskOwnerAndOngoingOrAcceptedTaskAssertion'),
                    array(
                    	array('user'), 
                    	'Ora\ReadModel\Task', 
                    	array('TaskManagement.Task.accept'), 
                    	'TaskOwnerAndCompletedTaskWithEstimationProcessCompletedAssertion'),
                    array(
                    	array('user'), 
                    	'Ora\ReadModel\Task', 
                    	array('TaskManagement.Task.assignShares'), 
                    	'TaskMemberAndAcceptedTaskAssertion'),
                ),
            ),
        ),
    ),
);
