<?php
namespace TaskManagement\Service;

use AcMailer\Service\MailService;
use Application\Service\UserService;
use TaskManagement\Service\NotifyMailListener;
use Application\Entity\User;
use TaskManagement\Task;
use UnitTest\Bootstrap;
use People\Organization;
use TaskManagement\Stream;
use TaskManagement\Entity\Task as ReadModelTask;
use TaskManagement\Entity\TaskMember;
use TaskManagement\Entity\TaskManagement\Entity;


class NotifyMailListenerTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var NotifyMailListener
	 */
	protected $listener;
	
	protected  $mailService;
	protected  $userService;
	
	protected $task;
	protected $owner;
	protected $member;
	protected $trigger; // estimation or share
	
	/**
	 * @var \Guzzle\Http\Client
	 */
	private $mailcatcher;
	
	protected function setUp(){
			
		$serviceManager = Bootstrap::getServiceManager();
		
		//Task Owner
		$this->owner = User::create();
		$this->owner->setFirstname("Owner_Firstname");
		$this->owner->setEmail("task_owner@oraproject.org");
				
		//Task Member
		$this->member = User::create();
		$this->member->setFirstname("Member_Firstname");
		$this->member->setLastname("Member_Lastname");
				
		//Organization & Stream for Task Creation
		$organization = Organization::create('Organization_test', $this->owner);
		$this->stream = Stream::create($organization, 'Steram_test', $this->owner);
		
		$this->task = Task::create($this->stream, 'Test TaskSubject', $this->owner);
		$this->task->addMember($this->owner, Task::ROLE_OWNER);
		//$this->task->addMember($this->member);
		
		//MailService
		$this->mailService = $serviceManager->get('AcMailer\Service\MailService');
		//MockUserService
		$this->userService = $this->getMockBuilder('Application\Service\UserService')->getMock();
		$this->userService->method('findUser')->will($this->returnValue($this->owner));
		
		$this->listener = new NotifyMailListener($this->mailService, $this->userService);
		
		$this->mailcatcher = new \Guzzle\Http\Client('http://127.0.0.1:1080');
		$this->cleanEmailMessages();
		
	}
	
	protected function tearDown(){
		$this->cleanEmailMessages();
	}
	
	public function testSendMail_EstimationAdded(){
		$this->trigger = 'estimation';
		$this->assertTrue($this->listener->sendEstimationAddedInfoMail($this->task, $this->member));
	}
	
	public function testSendMail_SharesAssigned(){
		$this->trigger = 'share';
		$this->assertTrue($this->listener->sendSharesAssignedInfoMail($this->task, $this->member));
	}
	
	public function testSendEmailNotificationForAssignmentOfShares(){
	
		$_SERVER['SERVER_NAME'] = 'example.com';
		
		$taskToNotify = $this->setupTaskWithMember();
		$this->listener->remindAssignmentOfShares($taskToNotify);	
		$emails = $this->getEmailMessages();	
		$this->assertNotEmpty($emails);
		$this->assertEquals(1, count($emails));
		$this->assertEmailSubjectEquals('O.R.A. - your contribution is required!', $emails[0]);
		$this->assertEmailHtmlContains('new book', $emails[0]);
		$this->assertEmailHtmlContains('http://example.com/task-management#11111ab-1111-1111-1111-11111111c500', $emails[0]);
		$this->assertNotEmpty($emails[0]->recipients);
		$this->assertEquals($emails[0]->recipients[0], '<doriangray@email.com>');

		unset($_SERVER['SERVER_NAME']);
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
		 
		$taskMember = User::create();
		$taskMember->setFirstname('Gray');
		$taskMember->setLastname('Dorian');
		$taskMember->setEmail('doriangray@email.com');
		 
		$task = new ReadModelTask('11111ab-1111-1111-1111-11111111c500');
		$task->setSubject('new book');
		$task->addMember($taskMember, TaskMember::ROLE_MEMBER, $taskMember, new \DateTime());

		return $task;
	}
}
