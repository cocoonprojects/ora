<?php
namespace Ora\ProjectManagement;

use Rhumsaa\Uuid\Uuid;
use Ora\User\User;
use Ora\User\UserService;
use Ora\ReadModel\Organization;

class MockProjectService implements ProjectService {
	
	/**
	 * 
	 * @var UserService
	 */
	private $userService;
	private $entityManager;
	
	public function __construct(UserService $userService, $entityManager)
	{
		$this->userService = $userService;
		$this->entityManager = $entityManager;
	}
	
	public function getProject($id) {
		try {
			$projectId = Uuid::fromString($id);
			$user = $this->userService->findUser('20000000-0000-0000-0000-000000000000');
			$rv = new Project($projectId, $user);
			$rv->setSubject('First project');
			return $rv;
		} catch(\InvalidArgumentException $e) {
			return null;
		}
	}
	
	public function findOrganizationProjects(Organization $organization)
	{
		$projects = $this->entityManager
					     ->getRepository('Ora\ReadModel\Project')
					     ->findBy(array('id' => '00000000-1000-0000-0000-000000000000'));
					     		
		return $projects;		
	}
}