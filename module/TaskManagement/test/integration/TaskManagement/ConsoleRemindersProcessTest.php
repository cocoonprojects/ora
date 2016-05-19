<?php

namespace TaskManagement;

use TaskManagement\Controller\Console\RemindersController;
use PHPUnit_Framework_TestCase;
use Guzzle\Http\Client;
use IntegrationTest\Bootstrap;
use Prooph\EventStore\EventStore;
use Application\Entity\User;
use People\Entity\Organization;
use TaskManagement\Entity\Task as EntityTask;
use TaskManagement\Entity\Stream;
use TaskManagement\Entity\TaskMember;
use TaskManagement\Entity\Vote;
use TaskManagement\Service\TaskService;
use Zend\Console\Request as ConsoleRequest;
use TaskManagement\Service\MailService;




class ConsoleRemindersProcessTest extends \PHPUnit_Framework_TestCase {

	private $controller;
	private $owner;
	private $member;
	private $task;
	private $transactionManager;
	private $taskService;
	private $mailService;

	protected function setUp()
	{
		$serviceManager = Bootstrap::getServiceManager();
		$this->mailService = $serviceManager->get('AcMailer\Service\MailService');


		$this->mailcatcher = new Client('http://127.0.0.1:1080');

		$this->organization = new Organization('1');
		$this->organization->setName('Organization Name');

		$this->stream = new Stream('1', $this->organization);
		$this->stream->setSubject("Stream subject");

		$this->owner = User::create();
		$this->owner->setFirstname('John');
		$this->owner->setLastname('Doe');
		$this->owner->setEmail('john.doe@foo.com');
		$this->owner->addMembership($this->organization);

		$this->member = User::create();
		$this->member->setFirstname('Jane');
		$this->member->setLastname('Doe');
		$this->member->setEmail('jane.doe@foo.com');
		$this->member->addMembership($this->organization);

		$this->task = new EntityTask('1', $this->stream);
		$this->task->setSubject('Lorem Ipsum Sic Dolor Amit');
		$this->task->addMember($this->owner, TaskMember::ROLE_OWNER, $this->owner, new \DateTime());
		$this->task->addMember($this->member, TaskMember::ROLE_MEMBER, $this->member, new \DateTime());

		$vote = new Vote(new \DateTime('today'));
		$vote->setValue(1);
		$this->task->addApproval($vote, $this->owner, new \DateTime('today'), 'Voto a favore');
				
		//Task Service Mock
		$this->taskService = $this->getMockBuilder(TaskService::class)->getMock();

		$this->controller = new RemindersController($this->taskService, $this->mailService);
		$this->request	= new ConsoleRequest();

		$this->cleanEmailMessages();		
	}	

	
	protected function cleanEmailMessages()
	{
		$request = $this->mailcatcher->delete('/messages');
		$response = $request->send();
	}
	
	protected function getEmailMessages()
	{
		$request = $this->mailcatcher->get('/messages');
		$response = $request->send();
		$json = json_decode($response->getBody());
		return $json;
	}

	public function assertEmailHtmlContains($needle, $email, $description = '')
	{
		$request = $this->mailcatcher->get("/messages/{$email->id}.html");
		$response = $request->send();
		$this->assertContains($needle, (string)$response->getBody(), $description);
	}

	public function testSendNotificationToUserWhoDidntVote()
	{
		$this->taskService
			->method('findIdeasCreatedBetween')
			->willReturn([$this->task]);

		$this->controller->sendAction();

		$emails = $this->getEmailMessages();

		$this->assertNotEmpty($emails);
		$this->assertEquals(1, count($emails));
		$this->assertContains($this->task->getSubject(), $emails[0]->subject);
		$this->assertEmailHtmlContains('approval', $emails[0]);
		$this->assertNotEmpty($emails[0]->recipients);
		$this->assertEquals($emails[0]->recipients[0], '<jane.doe@foo.com>');
	}
}
