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
		$organization = new ReadModelOrganization ( '1' );
		$organization->setName ( "OrganizationName" );
		
		$user = User::create ();
		$user->addMembership ( $organization, User::ROLE_ADMIN );
		$this->setupLoggedUser ( $user );
		
		$this->controller->getOrganizationService ()->method ( 'findOrganization' )->with ( $organization->getId () )->willReturn ( $organization );
		
		$userProfile = User::create ();
		$userProfile->setFirstname ( 'userFirstname' );
		$userProfile->setLastname ( 'userLastName' );
		$userProfile->setPicture ( 'picture_url' );
		$userProfile->setEmail ( 'userprofile@oraproject.org' );
		$userProfile->addMembership ( $organization, User::ROLE_USER );
		
		$this->controller->getUserService ()->method ( 'findUser' )->with ( $userProfile->getId () )->willReturn ( $userProfile );
		
		$actualBalance = 2000; // Fake balance, not used
		$balance = new Balance ( $actualBalance, new \DateTime () );
		
		$account = new PersonalAccount ( '1', $organization );
		$account->setBalance ( $balance );
		$account->addHolder ( $userProfile );
		
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
		
		$this->assertNotEmpty ( $arrayResult ['_embedded'] ['ora:organization-membership'] ['organization'] );
		$this->assertArrayHasKey ( 'id', $arrayResult ['_embedded'] ['ora:organization-membership'] ['organization'] );
		$this->assertArrayHasKey ( 'name', $arrayResult ['_embedded'] ['ora:organization-membership'] ['organization'] );
		$this->assertEquals ( $organization->getId (), $arrayResult ['_embedded'] ['ora:organization-membership'] ['organization'] ['id'] );
		$this->assertEquals ( $organization->getName (), $arrayResult ['_embedded'] ['ora:organization-membership'] ['organization'] ['name'] );
		$this->assertArrayHasKey ( 'role', $arrayResult ['_embedded'] ['ora:organization-membership'] );
		$this->assertEquals ( User::ROLE_USER , $arrayResult ['_embedded'] ['ora:organization-membership'] ['role'] );
		
		$this->assertArrayHasKey ( 'createdAt', $arrayResult ['_embedded'] ['ora:organization-membership'] );
		$this->assertArrayHasKey ( 'createdBy', $arrayResult ['_embedded'] ['ora:organization-membership'] );
		
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
		$organization = new ReadModelOrganization ( '1' );
		
		$user = User::create ();
		$user->addMembership($organization, User::ROLE_USER);
		$this->setupLoggedUser ( $user );
		
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
}