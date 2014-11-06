<?php
namespace Ora\ProjectManagement;

use Rhumsaa\Uuid\Uuid;
use Ora\User\User;
use Ora\User\UserService;

class MockProjectService implements ProjectService {
	
	/**
	 * 
	 * @var UserService
	 */
	private $userService;
	
	public function __construct(UserService $userService)
	{
		$this->userService = $userService;
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
}