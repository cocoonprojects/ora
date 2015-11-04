<?php
namespace TaskManagement\Controller;

use Application\Entity\User;
use Application\Service\UserService;
use People\Entity\Organization;
use People\Entity\OrganizationMembership;
use People\Service\OrganizationService;
use Rhumsaa\Uuid\Uuid;
use TaskManagement\Service\TaskService;
use ZFX\Test\Controller\ControllerTest;

/**
 * Class MemberStatsControllerTest
 * @package TaskManagement\Controller
 * @group wip
 */
class MemberStatsControllerTest extends ControllerTest
{
	/**
	 * @var Organization
	 */
	private $organization;
	/**
	 * @var User
	 */
	private $member;

	protected function setupController() {
		$this->organization = new Organization(Uuid::uuid4()->toString());

		$orgService = $this->getMockBuilder ( OrganizationService::class )->getMock ();
		$orgService
			->expects($this->once())
			->method ( 'findOrganization' )
			->with ( $this->organization->getId () )
			->willReturn ( $this->organization );

		$this->member = User::create();

		return new MemberStatsController( $orgService,
			$this->getMockBuilder ( TaskService::class )->getMock (),
			$this->getMockBuilder ( UserService::class )->getMock ()
		);
	}

	protected function setupRouteMatch() {
		return [
			'orgId' => $this->organization->getId(),
			'controller' => 'member-stats'
		];
	}

	public function testGetAsAnonymous() {
		$this->setupAnonymous();

		$this->routeMatch->setParam ( 'id', $this->member->getId() );

		$this->controller->dispatch ( $this->request );
		$response = $this->controller->getResponse ();

		$this->assertEquals (401, $response->getStatusCode () );
	}

	public function testGetAsUnauthorizedUser() {
		$user = User::create();
		$this->setupLoggedUser($user);

		$this->routeMatch->setParam ( 'id', $this->member->getId() );

		$this->controller->dispatch ( $this->request );
		$response = $this->controller->getResponse ();

		$this->assertEquals (403, $response->getStatusCode () );
	}

	public function testGetANotExistingMember() {
		$user = User::create();
		$user->addMembership($this->organization);
		$this->setupLoggedUser($user);

		$this->routeMatch->setParam ( 'id', $this->member->getId() );

		$this->controller->dispatch ( $this->request );
		$response = $this->controller->getResponse ();

		$this->assertEquals(404, $response->getStatusCode () );
	}

	public function testGetUserTaskMetrics() {
		$user = User::create();
		$user->addMembership($this->organization);
		$this->setupLoggedUser($user);

		$this->member->addMembership ( $this->organization, OrganizationMembership::ROLE_MEMBER );

		$this->controller->getTaskService()
			->method ( 'findMemberStats' )
			->with ( $this->organization, $this->member->getId())
			->willReturn ([
				'membershipsCount' => 5,
				'ownershipsCount' => 2,
				'creditsCount' => 10000,
				'averageDelta' => -0.0017
			]);

		$this->controller->getUserService()
			->method ( 'findUser' )
			->with ( $this->member->getId() )
			->willReturn ( $this->member );

		$this->routeMatch->setParam ( 'id', $this->member->getId() );

		$result = $this->controller->dispatch ( $this->request );
		$response = $this->controller->getResponse ();

		$this->assertEquals ( 200, $response->getStatusCode () );
		$arrayResult = json_decode ( $result->serialize (), true );
		$this->assertEquals( 2, $arrayResult['ownershipsCount'] );
		$this->assertEquals( 5, $arrayResult['membershipsCount'] );
		$this->assertEquals ( 10000, $arrayResult['creditsCount'] );
		$this->assertEquals ( -0.0017, $arrayResult['averageDelta'] );
	}
}