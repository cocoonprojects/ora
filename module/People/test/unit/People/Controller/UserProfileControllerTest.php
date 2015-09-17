<?php
namespace People\Controller;

use People\Service\OrganizationService;
use Application\Service\UserService;
use Accounting\Service\AccountService;
use People\Controller\UserProfileController;
use ZFX\Test\Controller\ControllerTest;
use People\Entity\Organization as ReadModelOrganization;
use Application\Entity\User;
use People\Entity\OrganizationMembership;
use Accounting\Entity\PersonalAccount;
use Doctrine\Common\Collections\ArrayCollection;
use Accounting\Entity\Balance;

class UserProfileControllerTest extends ControllerTest {
	
	protected function setupController() {
		$orgService = $this->getMockBuilder ( OrganizationService::class )->getMock ();
		$userService = $this->getMockBuilder ( UserService::class )->getMock ();
		$accountService = $this->getMockBuilder ( AccountService::class )->getMock ();
		
		return new UserProfileController ( $orgService, $userService, $accountService );
	}
	
	protected function setupRouteMatch() {
		return array (
				'controller' => 'user-profiles' 
		);
	}
	
	public function testGetUserProfile() {
		$user = User::create ();
		$this->setupLoggedUser ( $user );
		
		$organization = new ReadModelOrganization ( '1' );
		$organization->setName ( "OrganizationName" );
		
		$this->controller->getOrganizationService ()->expects ( $this->once () )->method ( 'findOrganization' )->with ( $organization->getId () )->willReturn ( $organization );
		
		$membership = $this->getMockBuilder ( OrganizationMembership::class )->disableOriginalConstructor ()->getMock ();
		$membership->method ( 'getRole' )->willReturn ( User::ROLE_ADMIN ); // Fake role
		
		$userProfile = $this->getMockBuilder ( User::class )->disableOriginalConstructor ()->getMock ();
		$userProfile->method ( 'getId' )->willReturn ( '60000000-0000-0000-0000-000000000000' );
		$userProfile->method ( 'getFirstname' )->willReturn ( 'userFirstname' );
		$userProfile->method ( 'getLastname' )->willReturn ( 'userLastName' );
		$userProfile->method ( 'getPicture' )->willReturn ( 'picture_url' );
		$userProfile->method ( 'getEmail' )->willReturn ( 'userprofile@oraproject.org' );
		$userProfile->method ( 'getMembershipOf' )->willReturn ( $membership );
		
		$this->controller->getUserService ()->method ( 'findUser' )->with ( $userProfile->getId () )->willReturn ( $userProfile );
		
		$actualBalance = 2000;
		$balance = $this->getMockBuilder ( Balance::class )->disableOriginalConstructor ()->getMock ();
		$balance->method ( 'getValue' )->willReturn ( $actualBalance );
		
		$account = $this->getMockBuilder ( PersonalAccount::class )->disableOriginalConstructor ()->getMock ();
		$account->method ( 'getBalance' )->willReturn ( $balance );
		$account->method ( 'getTransactions' )->willReturn ( new ArrayCollection () );
		
		$this->controller->getAccountService ()->method ( 'findPersonalAccount' )->willReturn ( $account );
		
		$this->routeMatch->setParam ( 'orgId', $organization->getId () );
		$this->routeMatch->setParam ( 'id', $userProfile->getId () );
		
		$result = $this->controller->dispatch ( $this->request );
		$response = $this->controller->getResponse ();
		
		$this->assertEquals ( 200, $response->getStatusCode () );
		$arrayResult = json_decode ( $result->serialize (), true );
		
		$this->assertEquals ( $userProfile->getId (), $arrayResult ['id'] );
		$this->assertEquals ( $userProfile->getFirstname (), $arrayResult ['firstname'] );
		$this->assertEquals ( $userProfile->getLastname (), $arrayResult ['lastname'] );
		$this->assertEquals ( $userProfile->getPicture (), $arrayResult ['picture'] );
		$this->assertEquals ( $userProfile->getEmail (), $arrayResult ['email'] );
		
		$this->assertNotEmpty ( $arrayResult ['_embedded'] ['organization'] );
		$this->assertArrayHasKey ( 'id', $arrayResult ['_embedded'] ['organization'] );
		$this->assertArrayHasKey ( 'name', $arrayResult ['_embedded'] ['organization'] );
		$this->assertArrayHasKey ( 'role', $arrayResult ['_embedded'] ['organization'] );
		$this->assertEquals ( $organization->getId (), $arrayResult ['_embedded'] ['organization'] ['id'] );
		$this->assertEquals ( $organization->getName (), $arrayResult ['_embedded'] ['organization'] ['name'] );
		$this->assertEquals ( $membership->getRole (), $arrayResult ['_embedded'] ['organization'] ['role'] );
		
		$this->assertNotEmpty ( $arrayResult ['_embedded'] ['credits'] );
		$this->assertEquals ( $actualBalance, $arrayResult ['_embedded'] ['credits'] ['balance'] );
		$this->assertEquals ( 0, $arrayResult ['_embedded'] ['credits'] ['total'] );
		$this->assertEquals ( 0, $arrayResult ['_embedded'] ['credits'] ['last3M'] );
		$this->assertEquals ( 0, $arrayResult ['_embedded'] ['credits'] ['last6M'] );
		$this->assertEquals ( 0, $arrayResult ['_embedded'] ['credits'] ['lastY'] );
	}
	
	public function testGetUserProfileAsAnonymous() {
		$this->setupAnonymous ();
		
		$organization = new ReadModelOrganization ( '1' );
		
		$this->controller->getOrganizationService ()->expects ( $this->once () )->method ( 'findOrganization' )->with ( $organization->getId () )->willReturn ( $organization );
		
		// Target User
		$userProfile = $this->getMockBuilder ( User::class )->getMock ();
		$userProfile->method ( 'getId' )->willReturn ( '60000000-0000-0000-0000-000000000000' );
		
		$this->routeMatch->setParam ( 'orgId', $organization->getId () );
		$this->routeMatch->setParam ( 'id', $userProfile->getId () );
		
		$result = $this->controller->dispatch ( $this->request );
		$response = $this->controller->getResponse ();
		
		$this->assertEquals ( 401, $response->getStatusCode () );
	}
	
	public function testGetUserProfileWithoutOrganizationId() {
		$user = User::create ();
		$this->setupLoggedUser ( $user );
		
		$userProfile = $this->getMockBuilder ( User::class )->disableOriginalConstructor ()->getMock ();
		$userProfile->method ( 'getId' )->willReturn ( '60000000-0000-0000-0000-000000000000' );
		
		$this->routeMatch->setParam ( 'id', $userProfile->getId () );
		
		$result = $this->controller->dispatch ( $this->request );
		$response = $this->controller->getResponse ();
		
		$this->assertEquals ( 400, $response->getStatusCode () );
	}
	
	public function testGetUserProfileWithoutUserId() {
		$user = User::create ();
		$this->setupLoggedUser ( $user );
		
		$organization = new ReadModelOrganization ( '1' );
		
		$this->routeMatch->setParam ( 'orgId', $organization->getId () );
		
		$result = $this->controller->dispatch ( $this->request );
		$response = $this->controller->getResponse ();
		
		$this->assertEquals ( 404, $response->getStatusCode () ); // Routing Error:404
	}
	
	public function testGetUserProfileWithWrongOrganizationId() {
		$user = User::create ();
		$this->setupLoggedUser ( $user );
		
		$this->controller->getOrganizationService ()->expects ( $this->once () )->method ( 'findOrganization' )->with ( $this->equalTo ( 'xxx' ) )->willReturn ( null );
		
		$this->routeMatch->setParam ( 'orgId', 'xxx' );
		
		$result = $this->controller->dispatch ( $this->request );
		$response = $this->controller->getResponse ();
		
		$this->assertEquals ( 404, $response->getStatusCode () );
	}
	
	public function testGetUserProfileWithWrongUserId() {
		$user = User::create ();
		$this->setupLoggedUser ( $user );
		
		$organization = new ReadModelOrganization ( '1' );
		
		$this->controller->getOrganizationService ()->method ( 'findOrganization' )->with ( $organization->getId () )->willReturn ( $organization );
		
		$userProfile = $this->getMockBuilder ( User::class )->disableOriginalConstructor ()->getMock ();
		$userProfile->method ( 'getId' )->willReturn ( '60000000-0000-0000-0000-000000000000' );
		
		$this->controller->getUserService ()->method ( 'findUser' )->with ( $userProfile->getId () )->willReturn ( null );
		
		$this->routeMatch->setParam ( 'orgId', $organization->getId () );
		$this->routeMatch->setParam ( 'id', $userProfile->getId () );
		
		$result = $this->controller->dispatch ( $this->request );
		$response = $this->controller->getResponse ();
		
		$this->assertEquals ( 404, $response->getStatusCode () );
	}
	
	public function testGetUserProfileWithNoMembership() {
		$user = User::create ();
		$this->setupLoggedUser ( $user );
		
		$organization = new ReadModelOrganization ( '1' );
		
		$this->controller->getOrganizationService ()->method ( 'findOrganization' )->with ( $organization->getId () )->willReturn ( $organization );
		
		$userProfile = $this->getMockBuilder ( User::class )->disableOriginalConstructor ()->getMock ();
		$userProfile->method ( 'getId' )->willReturn ( '60000000-0000-0000-0000-000000000000' );
		$userProfile->method ( 'getMembershipOf' )->willReturn ( null );
		
		$this->controller->getUserService ()->method ( 'findUser' )->with ( $userProfile->getId () )->willReturn ( $userProfile );
		
		$this->routeMatch->setParam ( 'orgId', $organization->getId () );
		$this->routeMatch->setParam ( 'id', $userProfile->getId () );
		
		$result = $this->controller->dispatch ( $this->request );
		$response = $this->controller->getResponse ();
		
		$this->assertEquals ( 404, $response->getStatusCode () );
	}
}