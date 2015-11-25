<?php 

namespace Kanbanize;


use Guzzle\Http\Client;
use IntegrationTest\Bootstrap;
use PHPUnit_Framework_TestCase;
use Prooph\EventStore\EventStore;
use Kanbanize\Controller\ImportsController;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Uri\Http;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Zend\Mvc\Router\RouteMatch;
use ZFX\Test\Authentication\AdapterMock;

class MailNotificationImportTest extends \PHPUnit_Framework_TestCase{

	protected $controller;
	protected $request;
	protected $response;
	protected $routeMatch;
	protected $event;

	public function setup(){
		$serviceManager = Bootstrap::getServiceManager();
		
		//Clean EmailMessages
		$this->mailcatcher = new Client('http://127.0.0.1:1080');
		$this->cleanEmailMessages();
		
		$userService = $serviceManager->get('Application\UserService');
		$this->user = $userService->findUser('60000000-0000-0000-0000-000000000000');
		
		$orgService = $serviceManager->get('People\OrganizationService');
		$importDirector = $serviceManager->get('Kanbanize\ImportDirector');
		$notificationService = $serviceManager->get('TaskManagement\NotifyMailListener');
		$this->controller = new ImportsController($orgService, $importDirector, $notificationService);
		$this->request	= new Request();
		$this->routeMatch = new RouteMatch(array('controller' => 'kanbanize-import'));
		$this->event	  = new MvcEvent();
		$config = $serviceManager->get('Config');
		$routerConfig = isset($config['router']) ? $config['router'] : array();
		$router = $serviceManager->get('HttpRouter');
		$router->setRequestUri(new Http("http://example.com"));
		$this->event->setRouter($router);
		$this->event->setRouteMatch($this->routeMatch);
		$this->controller->setEvent($this->event);
		$this->controller->setServiceLocator($serviceManager);
		
		$adapter = new AdapterMock();
		$adapter->setEmail($this->user->getEmail());
		$authService = $serviceManager->get('Zend\Authentication\AuthenticationService');
		$authService->authenticate($adapter);
		
		$pluginManager = $serviceManager->get('ControllerPluginManager');
		$this->controller->setPluginManager($pluginManager);
	}
	
	public function testKanbanizeImportNotification() {
		$this->cleanEmailMessages();
		$this->routeMatch->setParam('orgId', '00000000-0000-0000-1000-000000000000');
		$this->request->setMethod('post');
		$result = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$organization = $this->controller->getOrganizationService()->findOrganization('00000000-0000-0000-1000-000000000000');
		$organizationMembershipsCount = $this->controller->getOrganizationService()->countOrganizationMemberships($organization);
		$emails = $this->getEmailMessages();

		$this->assertNotEmpty($emails);
		$this->assertEquals($organizationMembershipsCount, count($emails));
		$this->assertContains("A new import from Kanbanize as been completed", $emails[0]->subject);
		$this->assertEmailHtmlContains('Results summary', $emails[0]);
		$this->assertEmailHtmlContains('Created streams', $emails[0]);
		$this->assertEmailHtmlContains('Created tasks', $emails[0]);
		$this->assertEmailHtmlContains('Updated tasks', $emails[0]);
		$this->assertEmailHtmlContains('Deleted tasks', $emails[0]);
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
	
	public function getLastEmailMessage()
	{
		$messages = $this->getEmailMessages();
		if (empty($messages)) {
			$this->fail("No messages received");
		}
		// messages are in descending order
		return reset($messages);
	}
	
	public function assertEmailHtmlContains($needle, $email, $description = '')
	{
		$request = $this->mailcatcher->get("/messages/{$email->id}.html");
		$response = $request->send();
		$this->assertContains($needle, (string)$response->getBody(), $description);
	}
}
