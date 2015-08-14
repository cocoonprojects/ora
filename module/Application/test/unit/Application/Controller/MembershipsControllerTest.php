<?php
namespace Application\Controller;

use Application\Entity\User;
use People\Entity\Organization;
use People\Entity\OrganizationMembership;
use People\Service\OrganizationService;
use ZFX\Test\Controller\ControllerTest;

class MembershipsControllerTest extends ControllerTest
{
	protected function setupController()
	{
		$orgService = $this->getMockBuilder(OrganizationService::class)->getMock();
		return new MembershipsController($orgService);
	}
	
	protected function setupRouteMatch()
	{
		return ['controller' => 'memberships'];
	}
	
	public function testGetListAsAnonymous() {
		$this->setupAnonymous();
		
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(401, $response->getStatusCode());
	}
	
	public function testGetEmptyList()
	{
		$user = User::create();
		$this->setupLoggedUser($user);
		
		$this->controller->getOrganizationService()
		->expects($this->once())
		->method('findUserOrganizationMemberships')
		->with($this->equalTo($user))
		->willReturn(array());
		
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$arrayResult = json_decode($result->serialize(), true);
		
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertCount(0, $arrayResult['_embedded']['ora:organization-membership']);
		$this->assertEquals(0, $arrayResult['count']);
		$this->assertEquals(0, $arrayResult['total']);
	}
	
	public function testGetList() {
		$org1 = new Organization('1');
		$org1->setName('Pippo');

		$org2 = new Organization('2');
		$org2->setName('Pluto');
		
		$user = User::create();
		$this->setupLoggedUser($user);
		
		$membership1 = new OrganizationMembership($user, $org1);
		$membership1->setRole(OrganizationMembership::ROLE_ADMIN);
		$membership1->setCreatedAt(new \DateTime());
		$membership1->setCreatedBy($user);
		
		$membership2 = new OrganizationMembership($user, $org2);
		$membership2->setRole(OrganizationMembership::ROLE_MEMBER);
		$membership2->setCreatedAt(new \DateTime());
		$membership2->setCreatedBy($user);
		 
		$this->controller->getOrganizationService()
			->expects($this->once())
			->method('findUserOrganizationMemberships')
			->with($this->equalTo($user))
			->willReturn([$membership1, $membership2]);
		
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$arrayResult = json_decode($result->serialize(), true);
		
		$this->assertEquals(200, $response->getStatusCode());		 
		$this->assertCount(2, $arrayResult['_embedded']['ora:organization-membership']);
		$this->assertEquals(2, $arrayResult['count']);
		$this->assertEquals(2, $arrayResult['total']);
		$this->assertArrayHasKey('ora:organization-member', $arrayResult['_embedded']['ora:organization-membership'][0]['organization']['_links']);
	}
	
	public function testGetListAsNotMemberOfAnyOrg() {
		$user = User::create();
		$this->setupLoggedUser($user);
		
		$this->controller->getOrganizationService()
			->expects($this->once())
			->method('findUserOrganizationMemberships')
			->with($this->equalTo($user))
			->willReturn(array());
		
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		$arrayResult = json_decode($result->serialize(), true);
		
		$this->assertEquals(200, $response->getStatusCode());		 
		$this->assertArrayHasKey('_embedded', $arrayResult);
		$this->assertArrayHasKey('ora:organization-membership', $arrayResult['_embedded']);
		$this->assertCount(0, $arrayResult['_embedded']['ora:organization-membership']);
		$this->assertEquals(0, $arrayResult['count']);
		$this->assertEquals(0, $arrayResult['total']);
	}
}