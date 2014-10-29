<?php
namespace Ora\ProjectManagement;

use Ora\User\User;

class MockProjectService implements ProjectService {
	
	public function getProject($id) {
		if(!is_numeric($id)) {
			throw new \RuntimeException('Project '.$id.' doesn\'t exist');
		}
		return new Project($id, new \DateTime(), new User('1', new \DateTime(), null));
	}
}