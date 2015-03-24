<?php
return array(
	'router' => array(
		'routes' => array(
			'tasks-home' => array(
				'type' => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
					'route'	   => '/task-management',
					'defaults' => array(
						'controller' => 'TaskManagement\Controller\Index',
						'action'	 => 'index',
					),
				),
			),
			'streams' => array(
				'type' => 'Segment',
				'options' => array(
					'route'	   => '/task-management/streams[/:id]',
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
					'route'	   => '/task-management/tasks[/:id][/:controller]',
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
				'Ora\Task' => array(),
				'Ora\Stream' => array(),
			),
		),
		'rule_providers' => array(
			'BjyAuthorize\Provider\Rule\Config' => array(
				'allow' => array(
					array(
						array('user'), 
						'Ora\Stream', 
						array('TaskManagement.Task.create')),
					array(
						array('user'), 
						'Ora\Task', 
						array('TaskManagement.Task.showDetails'),
						'TaskManagement\MemberOfOrganizationAssertion'), 
					array(
						array('user'), 
						'Ora\Task', 
						array('TaskManagement.Task.join'), 
						'TaskManagement\OrganizationMemberNotTaskMemberAndNotCompletedTaskAssertion'),
					array(
						array('user'), 
						'Ora\Task', 
						array('TaskManagement.Task.estimate'), 
						'TaskManagement\MemberOfNotAcceptedTaskAssertion'),
					array(
						array('user'), 
						'Ora\Task', 
						array('TaskManagement.Task.unjoin'), 
						'TaskManagement\TaskMemberNotOwnerAndNotCompletedTaskAssertion'),
					array(
						array('user'), 
						'Ora\Task', 
						array('TaskManagement.Task.edit', 'TaskManagement.Task.delete'), 
						 'TaskManagement\TaskOwnerAndNotCompletedTaskAssertion'),
					array(
						array('user'), 
						'Ora\Task', 
						array('TaskManagement.Task.execute'), 
						'TaskManagement\OwnerOfOpenOrCompletedTaskAssertion'),
					array(
						array('user'), 
						'Ora\Task', 
						array('TaskManagement.Task.complete'), 
						'TaskManagement\TaskOwnerAndOngoingOrAcceptedTaskAssertion'),
					array(
						array('user'), 
						'Ora\Task', 
						array('TaskManagement.Task.accept'), 
						'TaskManagement\TaskOwnerAndCompletedTaskWithEstimationProcessCompletedAssertion'),
					array(
						array('user'), 
						'Ora\Task', 
						array('TaskManagement.Task.assignShares'), 
						'TaskManagement\TaskMemberAndAcceptedTaskAssertion'),
				),
			),
		),
	),
	'listeners' => array(
		'TaskManagement\StreamCommandsListener',
		'TaskManagement\TaskCommandsListener',
		'TaskManagement\TransferTaskSharesCreditsListener',
		'TaskManagement\CloseTaskListener',
	),
);
