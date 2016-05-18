<?php

namespace TaskManagement;

use TaskManagement\Controller\Console\RemindersController;
use PHPUnit_Framework_TestCase;
use Guzzle\Http\Client;
use IntegrationTest\Bootstrap;
use Prooph\EventStore\EventStore;
use Zend\Console\Request as ConsoleRequest;

class ConsoleRemindersProcessTest extends \PHPUnit_Framework_TestCase {

	private $controller;
	private $owner;
	private $member;
	private $task;
	private $transactionManager;

	protected function setUp()
	{
		$this->mailcatcher = new Client('http://127.0.0.1:1080');

		$this->controller = new RemindersController();
		$this->request	= new ConsoleRequest();

		$serviceManager = Bootstrap::getServiceManager();

		$userService = $serviceManager->get('Application\UserService');
		$this->owner = $userService->findUser('60000000-0000-0000-0000-000000000000');
		$this->member = $userService->findUser('70000000-0000-0000-0000-000000000000');
		
		$streamService = $serviceManager->get('TaskManagement\StreamService');
		$stream = $streamService->getStream('00000000-1000-0000-0000-000000000000');
		
		$taskService = $serviceManager->get('TaskManagement\TaskService');

		$this->transactionManager = $serviceManager->get('prooph.event_store');
		$this->transactionManager->beginTransaction();
		try {
			$task = Task::create($stream, 'Cras placerat libero non tempor', $this->owner);
			$task->addMember($this->owner, Task::ROLE_OWNER);
			$task->addApproval('1', $this->owner, 'Voto a favore');

			$this->task = $taskService->addTask($task);
		} catch (\Exception $e) {
			var_dump($e);
			$this->transactionManager->rollback();
			throw $e;
		}		
		$this->transactionManager->commit();

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

	public function testSendNotificationToUserWhoDidntVote()
	{
		$this->controller->sendAction();

		$emails = $this->getEmailMessages();
// var_dump($emails);

		$this->assertNotEmpty($emails);
	}
}
