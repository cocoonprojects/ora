<?php
namespace People\Controller;

use ZFX\Test\Controller\ControllerTest;
use People\Service\OrganizationService;
use Application\Entity\User;
use People\Entity\Organization as ReadModelOrganization;
use People\Entity\OrganizationMembership;

class MembersControllerTest extends ControllerTest
{
	protected function setupController()
	{
		$orgService = $this->getMockBuilder(OrganizationService::class)->getMock();
		return new MembersController($orgService);
	}
	
	protected function setupRouteMatch()
	{
		return array('controller' => 'members');
	}
	
	public function testGetListAsAnonymous()
	{
		$this->setupAnonymous();
		
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(401, $response->getStatusCode());
	}

	public function testGetListWithoutOrganizationId()
	{
		$user = User::create();
		$this->setupLoggedUser($user);
		
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(400, $response->getStatusCode());
	}

	public function testGetListWithWrongOrganizationId()
	{
		$user = User::create();
		$user->setRole(User::ROLE_USER);
		$this->setupLoggedUser($user);
		
		$this->controller->getOrganizationService()
			->expects($this->once())
			->method('findOrganization')
			->with($this->equalTo('xxx'))
			->willReturn(null);
		
		$this->routeMatch->setParam('orgId', 'xxx');
		
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(404, $response->getStatusCode());
	}

	public function testGetListWithNotAllowedUser()
	{
		$user = User::create();
		$user->setRole(User::ROLE_USER);
		$this->setupLoggedUser($user);
		
		$organization = new ReadModelOrganization('1');
		
		$this->controller->getOrganizationService()
			->expects($this->once())
			->method('findOrganization')
			->with($this->equalTo($organization->getId()))
			->willReturn($organization);
		
		$this->routeMatch->setParam('orgId', $organization->getId());
		
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(403, $response->getStatusCode());
	}

	public function testGetEmptyList()
	{
		$organization = new ReadModelOrganization('1');
		$user = User::create();
		$user->setRole(User::ROLE_USER);
		$user->addOrganizationMembership(new OrganizationMembership($user, $organization));
		$this->setupLoggedUser($user);
				
		$this->controller->getOrganizationService()
			->expects($this->once())
			->method('findOrganization')
			->with($this->equalTo($organization->getId()))
			->willReturn($organization);
		
		$this->controller->getOrganizationService()
			->expects($this->once())
			->method('findOrganizationMemberships')
			->with($this->equalTo($organization))
			->willReturn(array());
		
		$this->routeMatch->setParam('orgId', $organization->getId());
		
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(200, $response->getStatusCode());		 
		
		$arrayResult = json_decode($result->serialize(), true);
		$this->assertCount(0, $arrayResult['_embedded']['ora:organization-member']);
		$this->assertEquals(0, $arrayResult['count']);
		$this->assertEquals(0, $arrayResult['total']);
	}
	
	public function testGetList()
	{
		$organization = new ReadModelOrganization('1');
		
		$user = User::create();
		$user->setFirstname('John');
		$user->setLastname('Doe');
		$user->setRole(User::ROLE_USER);
		$memberships[] = new OrganizationMembership($user, $organization);
		
		$user->addOrganizationMembership($memberships[0]);
		$this->setupLoggedUser($user);
		
		$this->controller->getOrganizationService()
		->expects($this->once())
		->method('findOrganization')
		->with($this->equalTo($organization->getId()))
		->willReturn($organization);
		
		$user2 = User::create();
		$user2->setFirstname('Jane');
		$user2->setLastname('Doe');
		$memberships[] = new OrganizationMembership($user2, $organization, OrganizationMembership::ROLE_ADMIN);
		
		$this->controller->getOrganizationService()
		->expects($this->once())
		->method('findOrganizationMemberships')
		->with($this->equalTo($organization))
		->willReturn($memberships);
		
		$this->routeMatch->setParam('orgId', $organization->getId());
		
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(200, $response->getStatusCode());
		
		$arrayResult = json_decode($result->serialize(), true);
		$this->assertCount(2, $arrayResult['_embedded']['ora:organization-member']);
		$this->assertEquals(2, $arrayResult['count']);
		$this->assertEquals(2, $arrayResult['total']);
		$this->assertArrayHasKey('id', $arrayResult['_embedded']['ora:organization-member'][0]);
		$this->assertArrayHasKey('firstname', $arrayResult['_embedded']['ora:organization-member'][0]);
		$this->assertArrayHasKey('lastname', $arrayResult['_embedded']['ora:organization-member'][0]);
	}
}