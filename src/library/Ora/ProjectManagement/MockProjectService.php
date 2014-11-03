<?php
namespace Ora\ProjectManagement;

use Rhumsaa\Uuid\Uuid;
use Ora\User\User;

class MockProjectService implements ProjectService {
	
	public function getProject($id) {
		try {
			$projectId = Uuid::fromString($id);
			$user = User::create(Uuid::fromString('20000000-0000-0000-0000-000000000000'));
			$user->setFirstname('Paul');
			$user->setLastname('Smith');
			$user->setEmail('paul.smith@ora.local');
			$rv = new Project($projectId, $user);
			$rv->setSubject('First project');
			return $rv;
		} catch(\InvalidArgumentException $e) {
			return null;
		}
	}
}