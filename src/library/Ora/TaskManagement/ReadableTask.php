<?php 

namespace Ora\TaskManagement;

interface ReadableTask {
	
	
	public function getReadableMembers();
	
	public function getReadableEstimation($memberId);
	
}


