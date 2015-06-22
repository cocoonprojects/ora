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



class NotifyMailListenerTest extends \PHPUnit_Framework_TestCase {
	
	
	protected $listener;
	
	protected  $mailService;
	protected  $userService;
	
	protected $task;
	protected $owner;
	protected $member;
	protected $trigger; // estimation or share
	
	
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
		
		//NotificationMailListener
		$this->listener = new NotifyMailListener($this->mailService, $this->userService);
		
	}
	
	public function testSendMail_EstimationAdded(){
		$this->trigger = 'estimation';
		$this->assertTrue($this->listener->sendMail($this->task, $this->member, $this->trigger));
	}
	
	public function testSendMail_EstimationAdded_WithNullTask(){
		$this->trigger = 'estimation';
		$this->assertFalse($this->listener->sendMail(null, $this->member, $this->trigger));
	}
	
	public function testSendMail_EstimationAdded_WithNullMember(){
		$this->trigger = 'estimation';
		$this->assertFalse($this->listener->sendMail($this->task, null, $this->trigger));
	}
	
	public function testSendMail_EstimationAdded_WithNullTrigger(){
		$this->assertFalse($this->listener->sendMail($this->task, $this->member, null));
	}
	
	public function testSendMail_EstimationAdded_WithWrongTrigger(){
		$this->trigger = 'wrong_trigger';
		$this->assertFalse($this->listener->sendMail($this->task, $this->member, $this->trigger));
	}
	
	public function testSendMail_SharesAssigned(){
		$this->trigger = 'share';
		$this->assertTrue($this->listener->sendMail($this->task, $this->member, $this->trigger));
	}
	
	public function testSendMail_SharesAssigned_WithNullTask(){
		$this->trigger = 'share';
		$this->assertFalse($this->listener->sendMail(null, $this->member, $this->trigger));
	}
	
	public function testSendMail_SharesAssigned_WithNullMember(){
		$this->trigger = 'share';
		$this->assertFalse($this->listener->sendMail($this->task, null, $this->trigger));
	}
	
	public function testSendMail_SharesAssigned_WithNullTrigger(){
		$this->assertFalse($this->listener->sendMail($this->task, $this->member, null));
	}
	
}
