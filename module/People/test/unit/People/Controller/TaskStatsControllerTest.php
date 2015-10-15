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
use TaskManagement\Controller\TasksController;


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

		$endOn = (new \DateTime())->format("Y-m-d")." 23:59:59";
		$startOn = TasksController::getDefaultStartOn(new \DateTime())->format("Y-m-d")." 00:00:00";
		$this->controller->getOrganizationService ()
			->method ( 'findOrganization' )
			->with ( $organization->getId () )
			->willReturn ( $organization );

		$this->controller->getTaskService()
			->method ( 'countTasksOwnership' )
			->with ( $organization, $orgOwner->getId(), ["startOn" => $startOn, "endOn"=>$endOn])
			->willReturn ( 5 );

		$closedTask = new Task('1', new Stream('1', $organization));
		$closedTask->setSubject('Lorem Ipsum');
		$closedTask->addMember($orgOwner, TaskMember::ROLE_OWNER, $orgOwner, new \DateTime());
		$closedTask->addMember($orgMember, TaskMember::ROLE_MEMBER, $orgMember, new \DateTime());
		$estimation1 = new Estimation(800, new \DateTime());
		$estimation2 = new Estimation(1000, new \DateTime());
		$closedTask->getMember($orgOwner)->setEstimation($estimation1);
		$closedTask->getMember($orgMember)->setEstimation($estimation2);
		$closedTask->getMember($orgOwner)->setShare(0.5, new \DateTime());
		$closedTask->getMember($orgMember)->setShare(0.5, new \DateTime());
		$closedTask->setStatus(Task::STATUS_CLOSED);

		$this->controller->getTaskService()
			->method ( 'findTaskMemberInClosedTasks' )
			->with ( $organization, $orgOwner->getId(), ["startOn" => $startOn, "endOn"=>$endOn])
			->willReturn ( [$closedTask->getMember($orgOwner)] );

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
		$this->assertArrayHasKey ( '_embedded', $arrayResult );
		$this->assertArrayHasKey ( 'ora:task', $arrayResult ['_embedded'] );
		$this->assertCount(3, $arrayResult ['_embedded'] ['ora:task'] );
		$this->assertArrayHasKey ( 'ownershipsCount', $arrayResult ['_embedded'] ['ora:task'] );
		$this->assertArrayHasKey ( 'creditsCount', $arrayResult ['_embedded'] ['ora:task'] );
		$this->assertArrayHasKey ( 'averageDelta', $arrayResult ['_embedded'] ['ora:task'] );
		$this->assertEquals( 5, $arrayResult ['_embedded'] ['ora:task'] ['ownershipsCount'] );
		$this->assertEquals ( 450, $arrayResult ['_embedded'] ['ora:task'] ['creditsCount'] );
		$this->assertNull( $arrayResult ['_embedded'] ['ora:task'] ['averageDelta'] );

	}
}