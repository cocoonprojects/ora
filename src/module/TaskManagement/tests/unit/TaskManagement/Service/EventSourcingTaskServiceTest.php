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
use Zend\View\Renderer\RendererInterface;

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

// 		$renderer = new RendererInterface();
// 		$noMembersWithEmptyShares = array();		
// 		$this->assertFalse($this->taskService->notifyMembersForShareAssignment($taskMembersWithEmptyShares, $renderer, ''));
// 		//$response = $this->getLastEmailMessage();
// 		//$this->assertContains($needle, (string)$response->getBody(), $description);
// 		//$this->assertEquals($expected, $response->sender, $description);
// 		//$this->assertContains($needle, $response->recipients, $description);
// 		//$this->assertNotEmpty($this->getMessages());
		
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
