<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
return array (
		'router' => array (
				'routes' => array (
						'mail' => array (
								'type' => 'Zend\Mvc\Router\Http\Literal',
								'options' => array (
										'route' => '/mail',
										'defaults' => array (
												'controller' => 'MailNotification\Controller\Mail',
												'action' => 'mail' 
										) 
								) 
						),
						
						'sendMail' => array (
								'type' => 'Zend\Mvc\Router\Http\Literal',
								'options' => array (
										'route' => '/mail/mail-simple',
										'defaults' => array (
												'controller' => 'MailNotification\Controller\Mail',
												'action' => 'sendMail'
										)
								)
						),
						
						'sendMailLogin' => array (
								'type' => 'Zend\Mvc\Router\Http\Literal',
								'options' => array (
										'route' => '/mail/mail-login',
										'defaults' => array (
												'controller' => 'MailNotification\Controller\Mail',
												'action' => 'sendMailLogin'
										)
								)
						),
						'sendMailTaskNotification' => array (
								'type' => 'Zend\Mvc\Router\Http\Literal',
								'options' => array (
										'route' => '/mail/mail-task',
										'defaults' => array (
												'controller' => 'MailNotification\Controller\Mail',
												'action' => 'sendMailTaskNotification'
										)
								)
						),
				) 
		),
		'service_manager' => array (
				'abstract_factories' => array () 
		),
		'translator' => array (),
		
		'view_manager' => array (
				'display_not_found_reason' => true,
				'display_exceptions' => true,
				'doctype' => 'HTML5',
				'not_found_template' => 'error/404',
				'exception_template' => 'error/index',
				'template_map' => array (
						'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
						'error/404' => __DIR__ . '/../view/error/404.phtml',
						'error/index' => __DIR__ . '/../view/error/index.phtml',
						'mail-notification/mail/mail-sent' => __DIR__ . '/../view/mail-notification/mail/mail-sent.phtml',
						'mail-notification/mail/login-template' => __DIR__ . '/../view/mail-notification/mail/login-template.phtml' ,
						'mail-notification/mail/task-template' => __DIR__ . '/../view/mail-notification/mail/task-template.phtml'
				),
				'template_path_stack' => array (
						__DIR__ . '/../view' 
				) 
		) 
);
