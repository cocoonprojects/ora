<?php
namespace TaskManagement\Controller;

use Application\Entity\User;
use People\Entity\Organization as ReadModelOrganization;
use People\Organization;
use People\Service\OrganizationService;
use TaskManagement\Service\StreamService;
use TaskManagement\Stream;
use ZFX\Test\Controller\ControllerTest;

class StreamsControllerTest extends ControllerTest
{
	protected $task;
	protected $member1;
	protected $member2;
	protected $organization;

	protected function setupController()
	{
		$streamServiceStub = $this->getMockBuilder(StreamService::class)->getMock();
		$organizationServiceStub = $this->getMockBuilder(OrganizationService::class)->getMock();
		return new StreamsController($streamServiceStub, $organizationServiceStub);
	}
	
	protected function setupRouteMatch()
	{
		return ['controller' => 'streams'];
	}
	
	protected function setUp()
	{
		parent::setUp();
		$user = User::create();
		$user->setRole(User::ROLE_USER);
		
		$this->organization = new ReadModelOrganization('00000000');
		$user->addMembership($this->organization);
		
		$this->setupLoggedUser($user);
	}
	
	public function testCreateStream() {
		$organization = Organization::create('Cum sociis natoque penatibus et', $this->getLoggedUser());
		$readModelOrganization = new ReadModelOrganization($organization->getId());		
		$stream = Stream::create($organization, 'Vestibulum sed magna vitae velit', $this->getLoggedUser());
		
		$this->controller->getOrganizationService()
			->expects($this->once())
			->method('findOrganization')
			->with($readModelOrganization->getId())
			->willReturn($readModelOrganization);
		
		$this->controller->getOrganizationService()
			->expects($this->once())
			->method('getOrganization')
			->with($organization->getId())
			->willReturn($organization);
		
		$this->controller->getStreamService()
			->expects($this->once())
			->method('createStream')
			->willReturn($stream);
		
		$this->request->setMethod('post');
		$params = $this->request->getPost();
		$params->set('subject', 'Vestibulum sed magna vitae velit');
		
		$this->routeMatch->setParam('orgId', $readModelOrganization->getId());
		
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		 
		$this->assertEquals(201, $response->getStatusCode());
		$this->assertNotEmpty($response->getHeaders()->get('Location'));
	}
	
	public function testCreateStreamWithHtmlTagsInSubject() {
		$organization = Organization::create('Cum sociis natoque penatibus et', $this->getLoggedUser());
		$readModelOrganization = new ReadModelOrganization($organization->getId());
		$stream = Stream::create($organization, 'Vestibulum sedalert("A big problem") magna vitae velit', $this->getLoggedUser());
		
		$this->controller->getOrganizationService()
			->expects($this->once())
			->method('findOrganization')
			->with($readModelOrganization->getId())
			->willReturn($readModelOrganization);
		
		$this->controller->getOrganizationService()
			->expects($this->once())
			->method('getOrganization')
			->with($organization->getId())
			->willReturn($organization);
		
		$this->controller->getStreamService()
			->expects($this->once())
			->method('createStream')
			->with($organization, $this->equalTo('Vestibulum sedalert("A big problem") magna vitae velit'), $this->getLoggedUser())
			->willReturn($stream);
		
		$this->request->setMethod('post');
		$params = $this->request->getPost();
		$params->set('subject', 'Vestibulum sed<script>alert("A big problem")</script> magna vitae velit');
		
		$this->routeMatch->setParam('orgId', $readModelOrganization->getId());
		
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(201, $response->getStatusCode());
		$this->assertNotEmpty($response->getHeaders()->get('Location'));
	}
	
	public function testCreateStreamInNotExistingOrganization() {
		$this->controller->getOrganizationService()
			->expects($this->once())
			->method('findOrganization')
			->with('00000000-0000-0000-2000-000000000000')
			->willReturn(null);
		
		$this->request->setMethod('post');
		$params = $this->request->getPost();
		$params->set('subject', 'Vestibulum sed magna vitae velit');
		
		$this->routeMatch->setParam('orgId', '00000000-0000-0000-2000-000000000000');
		
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(404, $response->getStatusCode());
	}
	
	public function testCreateStreamWithoutOrganization() {
		$this->request->setMethod('post');
		$params = $this->request->getPost();
		$params->set('subject', 'Vestibulum sed magna vitae velit');
		
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		 
		$this->assertEquals(400, $response->getStatusCode());
	}

	public function testCreateStreamAsAnonymous() {
		$this->setupAnonymous();
		
		$this->controller->getOrganizationService()
			->expects($this->once())
			->method('findOrganization')
			->with($this->organization->getId())
			->willReturn($this->organization);
		
		$this->routeMatch->setParam('orgId', $this->organization->getId());
		
		$this->request->setMethod('post');
		
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		 
		$this->assertEquals(401, $response->getStatusCode());
	}
	
	public function testGetList() {
		$this->setupAnonymous();

		$this->controller->getOrganizationService()
			->expects($this->once())
			->method('findOrganization')
			->with($this->organization->getId())
			->willReturn($this->organization);
		
		$this->request->setMethod('get');
		
		$this->routeMatch->setParam('orgId', $this->organization->getId());
		
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(401, $response->getStatusCode());
	}
	
	public function testGetEmptyList() {
		
		$this->controller->getOrganizationService()
			->expects($this->once())
			->method('findOrganization')
			->with($this->organization->getId())
			->willReturn($this->organization);		
		
		$this->controller->getStreamService()
			->expects($this->once())
			->method('findStreams')
			->willReturn(array());
		
		$this->request->setMethod('get');
		
		$this->routeMatch->setParam('orgId', $this->organization->getId());
		
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$arrayResult = json_decode($result->serialize(), true);
		
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertArrayHasKey('_embedded', $arrayResult);
		$this->assertArrayHasKey('ora:stream', $arrayResult['_embedded']);
		$this->assertCount(0, $arrayResult['_embedded']['ora:stream']);
	}
	
	protected function getLoggedUser() {
		return $this->controller->identity();
	}
}