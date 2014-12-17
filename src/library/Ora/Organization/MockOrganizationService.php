<?php
namespace Ora\Organization;

use Rhumsaa\Uuid\Uuid;
use Ora\User\User;
use Ora\User\UserService;

class MockOrganizationService implements OrganizationService {
	
	/**
	 * 
	 * @var UserService
	 */
	private $userService;
	
	public function __construct(UserService $userService)
	{
		$this->userService = $userService;
	}
		
	public function findOrganization($id)
	{
		try {
			$organizationId = Uuid::fromString($id);
			$user = $this->userService->findUser('20000000-0000-0000-0000-000000000000');
			$rv = new Organization($organizationId, $user);
			$rv->setSubject('ORA');
			return $rv;
		} catch(\InvalidArgumentException $e) {
			return null;
		}
	
		return $organization;
	}
	
	
    public function findUserOrganizationMembership(User $user)
    {
        $organizationList = array();

        $user = $this->userService->findUser('20000000-0000-0000-0000-000000000000');

        $organizationId = Uuid::uuid4();
        $organization = new \Ora\ReadModel\Organization($organizationId, new \DateTime(), $user);
        $organization->setSubject('ORA');

        $organizationUser = new \Ora\ReadModel\OrganizationMembership($user, $organization);
        $organizationUser->setOrganizationRole('Member');

        $organizationList[] = $organizationUser;

        return $organizationList;
    }
    
    public function findOrganizationMembership(Organization $organization)
    {
    
    }    

}
