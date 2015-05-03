<?php
namespace People\Controller;

use ZFX\Test\Controller\ControllerTest;
use Application\Organization;
use Application\Entity\User;
use Application\Service\OrganizationService;
use Accounting\OrganizationAccount;

class OrganizationsControllerTest extends ControllerTest
{
	protected function setupController()
	{
		$orgService = $this->getMockBuilder(OrganizationService::class)->getMock();
		return new OrganizationsController($orgService);
	}
	
	protected function setupRouteMatch()
	{
		return array('controller' => 'organizations');
	}
	
	
	public function testCreate() {
		$user = User::create();
		$this->setupLoggedUser($user);
		
		$this->controller->getOrganizationService()
			->method('createOrganization')
			->willReturn(Organization::create('Fusce nec ullamcorper', $user));
		
		$this->request->setMethod('post');
		
		$params = $this->request->getPost();
		$params->set('name', 'Fusce nec ullamcorper');
		
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(201, $response->getStatusCode());
		$this->assertNotEmpty($response->getHeaders()->get('Location'));
	}

	public function testCreateWithoutName() {
		$user = User::create();
		$this->setupLoggedUser($user);
		
		$this->controller->getOrganizationService()
			->method('createOrganization')
			->willReturn(Organization::create(null, $user));
		
		$this->request->setMethod('post');
				
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(201, $response->getStatusCode());
		$this->assertNotEmpty($response->getHeaders()->get('Location'));
	}

	public function testCreateWithHtmlTagName() {
		$user = User::create();
		$this->setupLoggedUser($user);
		
		$this->controller->getOrganizationService()
			->expects($this->once())
			->method('createOrganization')
			->with($this->equalTo('alert("Say hi!")Fusce nec ullamcorper'))
			->willReturn(Organization::create('alert("Say hi!")Fusce nec ullamcorper', $user));
		
		$this->request->setMethod('post');
		
		$params = $this->request->getPost();
		$params->set('name', '<script>alert("Say hi!")</script>Fusce nec ullamcorper');

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(201, $response->getStatusCode());
		$this->assertNotEmpty($response->getHeaders()->get('Location'));
	}

	public function testCreateAsAnonymous() {
		$this->setupAnonymous();
		$this->request->setMethod('post');
		
		$params = $this->request->getPost();
		$params->set('name', 'Fusce nec ullamcorper');
		
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(401, $response->getStatusCode());		 
	}
}