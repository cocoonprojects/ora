<?php
namespace TaskManagement\Service;

use Prooph\EventStoreTest\TestCase;
use Prooph\EventStore\Stream\Stream as ProophStream;
use Prooph\EventStore\Stream\StreamName;
use Rhumsaa\Uuid\Uuid;
use Application\Entity\User;
use Application\Organization;
use TaskManagement\Stream;
use TaskManagement\Task;
<<<<<<< HEAD:module/TaskManagement/test/unit/TaskManagement/Service/EventSourcingTaskServiceTest.php
use Zend\View\Renderer\RendererInterface;
=======
use TaskManagement\Entity\Task as ReadModelTask;
>>>>>>> send notification email completed:src/module/TaskManagement/tests/unit/TaskManagement/Service/EventSourcingTaskServiceTest.php


class EventSourcingTaskServiceTest extends TestCase {
	
	/**
	 * 
	 * @var TaskService
	 */
	private $taskService;
	/**
	 * 
	 * @var User
	 */
	private $user;
	/**
	 * @var \Guzzle\Http\Client
	 */
	private $mailcatcher;

	
	protected function setUp() {
		parent::setUp();
		$entityManager = $this->getMock('\Doctrine\ORM\EntityManager', array('getRepository', 'getClassMetadata', 'persist', 'flush'), array(), '', false);
		$this->eventStore->beginTransaction();
		$this->eventStore->create(new ProophStream(new StreamName('event_task'), array()));
		$this->eventStore->commit();
		$this->taskService = new EventSourcingTaskService($this->eventStore, $entityManager);
		$this->user = User::create();
		$this->mailcatcher = new \Guzzle\Http\Client('http://127.0.0.1:1080');
		$this->cleanEmailMessages();
	}
	
	public function testNotifyMembersForShareAssigment() {
<<<<<<< HEAD:module/TaskManagement/test/unit/TaskManagement/Service/EventSourcingTaskServiceTest.php

		$taskToNotify = $this->setupTask();
		$this->user->setEmail('user@email.com');
		
		$taskToNotify->addMember($this->user, Task::ROLE_OWNER);
		$taskToNotify->addEstimation(1, $this->user);
		$taskToNotify->complete($this->user);
		$taskToNotify->accept($this->user);		
		
		$this->taskService->notifyMembersForShareAssignment($taskToNotify);
		
		//TODO: completare il test con le assertions

=======
		
		//TODO: riscrivere il test
>>>>>>> send notification email completed:src/module/TaskManagement/tests/unit/TaskManagement/Service/EventSourcingTaskServiceTest.php
		
	}
	
	protected function setupStream(){
		
		$organization = Organization::create('My brand new Orga', $this->user);
        return Stream::create($organization, 'Really useful stream', $this->user);
	}
	
	protected function setupTask(){
		
		$stream = $this->setupStream();
		return Task::create($stream, 'task subject', $this->user);		
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
    
    public function getLastEmailMessage()
    {
    	$messages = $this->getEmailMessages();
    	if (empty($messages)) {
    		$this->fail("No messages received");
    	}
    	// messages are in descending order
    	return reset($messages);
    }
}
