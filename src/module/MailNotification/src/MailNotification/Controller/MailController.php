<?php

namespace MailNotification\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\Mail;
use AcMailer\Service\MailService;

class MailController extends AbstractActionController {
	public function mailAction() {
		$view = new ViewModel ();
		$view->setVariable ( 'prova', 'prova' )->setVariable ( 'placeholder_User', 'placeholder_User' )->setVariable ( 'placeholder', 'placeholder' )->setVariable ( 'taskUser', 'TaskUser' )->setVariable ( 'taskTitle', 'TaskTitle' )->setVariable ( 'taskId', '10' )->setVariable ( 'taskDetail_1', 'taskDetail_1' )->setVariable ( 'taskDetail_2', 'taskDetail_2' )->setVariable ( 'taskDetail_3', 'taskDetail_3' );
		return $view;
	}
	
	public function sendMailAction() {
		$mailService = $this->getServiceLocator ()->get ( 'AcMailer\Service\MailService' );
		
		$message = $mailService->getMessage ();
		//$message->setFrom ( 'prova@oraproject.org', 'Test' );
		$message->setTo ( 'serianniabdon@gmail.com' );
		
		$mailService->setSubject ( 'Plain Text Mail' );
		$mailService->setBody('This is plain text mail.');
		
		try {
			$result = $mailService->send ();
		} catch ( \Exception $e ) {
			echo "Exception!! " . $e->getMessage ();
		}
		$view = new ViewModel ();
		$view->setTemplate('mail-notification/mail/mail-sent');
		$view->setVariable ( 'result', $result );
		return $view;
	}
	
	public function sendMailLoginAction() {
		$mailService = $this->getServiceLocator ()->get ( 'AcMailer\Service\MailService' );
		
		$message = $mailService->getMessage ();
		//$message->setFrom ( 'prova@oraproject.org', 'Test' );
		$message->setTo ( 'serianniabdon@gmail.com' );
		
		$mailService->setSubject ( 'Login Notification Mail' );
		$mailService->setTemplate ( 'mail-notification/mail/login-template', array (
				'placeholder_User' => 'placeholder_User' 
		) );
		
		try {
			$result = $mailService->send ();
		} catch ( \Exception $e ) {
			echo "Exception!! " . $e->getMessage ();
		}
		$view = new ViewModel ();
		$view->setTemplate('mail-notification/mail/mail-sent');
		$view->setVariable ( 'result', $result );
		return $view;
	}
	
	public function sendMailTaskNotificationAction() {
		$mailService = $this->getServiceLocator ()->get ( 'AcMailer\Service\MailService' );
		$message = $mailService->getMessage ();
		//$message->setFrom ( 'prova@oraproject.org', 'Test' );
		$message->setTo ( 'serianniabdon@gmail.com' );
		
		$mailService->setSubject ( 'Task Notification Mail' );
		$mailService->setTemplate ( 'mail-notification/mail/task-template', array (
				'placeholder' => 'placeholder',
				'taskUser' => 'TaskUser',
				'taskTitle' => 'TaskTitle',
				'taskId' => '10',
				'taskDetail_1' => 'taskDetail_1',
				'taskDetail_2' => 'taskDetail_2',
				'taskDetail_3' => 'taskDetail_3' 
		) );
		
		try {
			$result = $mailService->send ();
		} catch ( \Exception $e ) {
			echo "Exception!! " . $e->getMessage ();
		}
		$view = new ViewModel ();
		$view->setTemplate('mail-notification/mail/mail-sent');
		$view->setVariable ( 'result', $result );
		return $view;
	}
}