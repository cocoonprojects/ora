<?php
namespace TaskManagement\Service;

use Prooph\EventStoreTest\TestCase;
use Prooph\EventStore\Stream\Stream as ProophStream;
use Prooph\EventStore\Stream\StreamName;
use Rhumsaa\Uuid\Uuid;
use Application\Entity\User;
use People\Organization;
use TaskManagement\Stream;
use TaskManagement\Task;
use Zend\View\Renderer\PhpRenderer;


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

		$taskToNotify = $this->setupTask();
		$this->user->setEmail('user@email.com');
		
		$taskToNotify->addMember($this->user, Task::ROLE_OWNER);
		$taskToNotify->addEstimation(1, $this->user);
		$taskToNotify->complete($this->user);
		$taskToNotify->accept($this->user);				
		
		$_SERVER['SERVER_NAME'] = 'oraprojecttest';
		
		$this->taskService->setEmailTemplates(array('TaskManagement\NotifyMemebersForShareAssignment' => __DIR__.'/../../../../view/task-management/email_templates/hurryup-taskmember.phtml'));

		$this->taskService->notifyMembersForShareAssignment($taskToNotify, new PhpRenderer(), array($this->user));
				
		$emails = $this->getEmailMessages();
		
		// added only for travis debug
// 		var_dump($emails);
// 		die();
		
		
		$this->assertNotEmpty($emails);
		$this->assertEquals(1, count($emails));
		$this->assertEmailSubjectEquals('O.R.A. - your contribution is required!', $emails[0]);
		$this->assertEmailHtmlContains('task subject', $emails[0]);
		$this->assertNotEmpty($emails[0]->recipients);
		$this->assertEquals(1, count($emails[0]->recipients));
		$this->assertEquals($emails[0]->recipients[0], '<user@email.com>');
		
		unset($_SERVER['SERVER_NAME']);
		$this->cleanEmailMessages();
		
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
    
    public function assertEmailSubjectEquals($expected, $email, $description = '')
    {
    	$this->assertContains($expected, $email->subject, $description);
    }
    
    public function assertEmailHtmlContains($needle, $email, $description = '')
    {
    	$response = $this->mailcatcher->get("/messages/{$email->id}.html")->send();
    	$this->assertContains($needle, (string)$response->getBody(), $description);
    }
    
}
