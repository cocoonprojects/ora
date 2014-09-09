<?php 

namespace TasksManagement\Service;
use TasksManagement\Service\ProjectService;

class ProjectServiceImpl implements ProjectService{

	
	public function __construct(){	
		
	}
	
	public function addTask($projectId, $subject){
		
		//inserire evento di aggiunta task
		return array($projectId => 'new task added: '.$subject);
	}
		
}


