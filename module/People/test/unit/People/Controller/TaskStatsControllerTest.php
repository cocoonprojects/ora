<?php
namespace People\Controller;

use Application\Entity\User;
use Application\Service\UserService;
use People\Entity\Organization;
use People\Entity\OrganizationMembership;
use People\Service\OrganizationService;
use TaskManagement\Entity\Estimation;
use TaskManagement\Entity\Stream;
use TaskManagement\Entity\TaskMember;
use TaskManagement\Entity\Task;
use TaskManagement\Service\TaskService;
use ZFX\Test\Controller\ControllerTest;


class TaskStatsControllerTest extends ControllerTest{

	protected function setupController() {
		$orgService = $this->getMockBuilder ( OrganizationService::class )->getMock ();
		$taskService = $this->getMockBuilder ( TaskService::class )->getMock ();
		$userService = $this->getMockBuilder ( UserService::class )->getMock ();
		return new TaskStatsController( $orgService, $taskService, $userService );
	}

	protected function setupRouteMatch() {
		return array (
				'controller' => 'task-stats'
		);
	}

	public function testGetUserTaskMetrics() {
		$organization = new Organization( '00' );
		$organization->setName ( "New Orga" );

		$orgOwner = User::create();
		$orgOwner->addMembership ( $organization, OrganizationMembership::ROLE_ADMIN);
		$this->setupLoggedUser ( $orgOwner );

		$orgMember = User::create();
		$orgMember->addMembership ( $organization, OrganizationMembership::ROLE_MEMBER );

		$endOn = (new \DateTime())->setTime(23, 59, 59);
		$startOn = clone $endOn;
		$startOn->sub(new \DateInterval('P1Y'))->setTime(0, 0, 0);
		$this->controller->getOrganizationService ()
			->method ( 'findOrganization' )
			->with ( $organization->getId () )
			->willReturn ( $organization );

		$this->controller->getTaskService()
			->method ( 'findStatsForMember' )
			->with ( $organization, $orgOwner->getId(), ["startOn" => $startOn, "endOn"=>$endOn])
			->willReturn ([
				'membershipsCount' => 5,
				'ownershipsCount' => 2,
				'creditsCount' => 10000,
				'averageDelta' => -0.0017
			]);

		$this->controller->getUserService()
			->method ( 'findUser' )
			->with ( $orgOwner->getId() )
			->willReturn ( $orgOwner );

		$this->routeMatch->setParam ( 'orgId', $organization->getId () );
		$this->routeMatch->setParam ( 'id', $orgOwner->getId () );

		$result = $this->controller->dispatch ( $this->request );
		$response = $this->controller->getResponse ();

		$this->assertEquals ( 200, $response->getStatusCode () );
		$arrayResult = json_decode ( $result->serialize (), true );
		$this->assertArrayHasKey ( 'membershipsCount', $arrayResult);
		$this->assertArrayHasKey ( 'ownershipsCount', $arrayResult);
		$this->assertArrayHasKey ( 'creditsCount', $arrayResult);
		$this->assertArrayHasKey ( 'averageDelta', $arrayResult);
		$this->assertEquals( 2, $arrayResult['ownershipsCount'] );
		$this->assertEquals( 5, $arrayResult['membershipsCount'] );
		$this->assertEquals ( 10000, $arrayResult['creditsCount'] );
		$this->assertEquals ( -0.0017, $arrayResult['averageDelta'] );

	}
}