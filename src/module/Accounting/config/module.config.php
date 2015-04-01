<?php
return array(
	'router' => array(
		'routes' => array(
			'accounting-home' => array(
				'type' => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
					'route'	   => '/accounting/',
					'defaults' => array(
						'controller' => 'Accounting\Controller\Index',
						'action'	 => 'index',
					),
				),
			),
			'accounts' => array (
				'type'	  => 'segment',
				'options' => array (
					'route'		  => '/accounting/accounts[/:id][/:controller]',
					'constraints' => array (
						'id'	 => '[0-9a-z\-]+',
					),
					'defaults'	  => array (
						'__NAMESPACE__' => 'Accounting\Controller',
						'controller' => 'Accounts'
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
			'accounting' => __DIR__ . '/../view',
		),
	),
	'asset_manager' => array(
		'resolver_configs' => array(
			'paths' => array(
				'Accounting' => __DIR__ . '/../public',
			),
		),
	),
	
	'bjyauthorize'=> array(
		'resource_providers' => array(
			'BjyAuthorize\Provider\Resource\Config' => array(
				'Ora\Account' => array()		
			),
		),
		'rule_providers' => array(
			'BjyAuthorize\Provider\Rule\Config' => array(
				'allow' => array(
					array(
						array('user'), 
						'Ora\Account', 
						array('Accounting.Account.deposit'),
						'Accounting\AccountHolderAssertion'), 
					array(
						array('user'), 
						'Ora\Account', 
						array('Accounting.Account.statement'), 
						'Accounting\MemberOfOrganizationOrAccountHolder'), 
					array(
						array('user'), 
						'Ora\Account',
						array('Accounting.OrganizationAccount.deposit'),
						'Accounting\AccountHolderOfOrganizationAccountAssertion'),
				)
			)
		)
	),
	
	'listeners' => array(
		'Accounting\AccountCommandsListener',
		'Accounting\CreatePersonalAccountListener',
		'Accounting\CreateOrganizationAccountListener'
	)
);