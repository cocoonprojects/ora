<?php

namespace TaskManagement\Service;

use Zend\View\Renderer\PhpRenderer;
use Zend\View\Model\ViewModel;
use Zend\View\Resolver\TemplateMapResolver;
use Application\Entity\User;
use TaskManagement\Entity\Task;
use TaskManagement\Entity\TaskMember;


class NotificationServiceTest extends \PHPUnit_Framework_TestCase {
	

	/**
	 * @var \Guzzle\Http\Client
	 */
	private $mailcatcher;
	/**
	 * @var NotificationService
	 */
	private $notificationService;
	/**
	 * @var Application\Entity\User
	 */
	private $taskMember;
	/**
	 * @var TaskManagement\Entity\Task
	 */
	private $task;
	
	protected function setUp() {
		
		$emailTemplates = array('TaskManagement\RemindTemplateForAssignmentOfShares' => __DIR__.'/../../../../view/task-management/email_templates/reminder-assignment-shares.phtml');
		
		$this->notificationService = new NotificationService($emailTemplates);
		$this->mailcatcher = new \Guzzle\Http\Client('http://127.0.0.1:1080');
		$this->cleanEmailMessages();		
		$this->setupTaskWithMember();
		$_SERVER['SERVER_NAME'] = 'example.com';
	}
	
	protected function tearDown(){
		unset($_SERVER['SERVER_NAME']);
	}
	
	
	public function testSendEmailNotificationForAssignmentOfShares(){
		
		$this->notificationService->sendEmailNotificationForAssignmentOfShares($this->task, $this->taskMember);
		
		$emails = $this->getEmailMessages();
		
		$this->assertNotEmpty($emails);
		$this->assertEquals(1, count($emails));
		$this->assertEmailSubjectEquals('O.R.A. - your contribution is required!', $emails[0]);
		$this->assertEmailHtmlContains('new book', $emails[0]);
		$this->assertEmailHtmlContains('http://example.com/task-management#11111ab-1111-1111-1111-11111111c500', $emails[0]);
		$this->assertNotEmpty($emails[0]->recipients);
		$this->assertEquals($emails[0]->recipients[0], '<doriangray@email.com>');
		
	}
	
	
	
	protected function cleanEmailMessages()
	{
		$this->mailcatcher->delete('/messages')->send();
	}
	
	protected function getEmailMessages()
    {
        $jsonResponse = $this->mailcatcher->get('/messages')->send();
        return json_decode($jsonResponse->getBody());
    }
    
    protected function getLastEmailMessage()
    {
    	$messages = $this->getEmailMessages();
    	if (empty($messages)) {
    		$this->fail("No messages received");
    	}
    	// messages are in descending order
    	return reset($messages);
    }
    
    protected function assertEmailSubjectEquals($expected, $email, $description = '')
    {
    	$this->assertContains($expected, $email->subject, $description);
    }
    
    protected function assertEmailHtmlContains($needle, $email, $description = '')
    {
    	$response = $this->mailcatcher->get("/messages/{$email->id}.html")->send();
    	$this->assertContains($needle, (string)$response->getBody(), $description);
    }
    
    protected function setupTaskWithMember(){
    	
    	$this->taskMember = User::create();
    	$this->taskMember->setFirstname('Gray');
    	$this->taskMember->setLastname('Dorian');
    	$this->taskMember->setEmail('doriangray@email.com');
    	
    	$this->task = new Task('11111ab-1111-1111-1111-11111111c500');
    	$this->task->setSubject('new book');
    	$this->task->addMember($this->taskMember, TaskMember::ROLE_MEMBER, $this->taskMember, new \DateTime());
    	
    }
    
}