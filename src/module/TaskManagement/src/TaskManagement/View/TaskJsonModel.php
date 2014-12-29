<?php
namespace TaskManagement\View;

use Zend\View\Model\JsonModel;
use Zend\Json\Json;
use Ora\ReadModel\Task;
use Ora\ReadModel\Estimation;
use Ora\ReadModel\TaskMember;

class TaskJsonModel extends JsonModel
{
	private $url;
	
	public function __construct($urlPlugin) {
		$this->url = $urlPlugin;
	}
	
	public function serialize()
	{
		$resource = $this->getVariable('resource');

        if(is_array($resource)) {
			$representation['tasks'] = [];
			foreach ($resource as $r) {
				$representation['tasks'][] = $this->serializeOne($r);
			}
		} else {
			$representation = $this->serializeOne($resource);
		}
		return Json::encode($representation);
	}


	private function serializeOne(Task $t) {
		$rv = [
			'id' => $t->getId (),
			'createdAt' => date_format($t->getCreatedAt(), 'c'),
			'status' => $t->getStatus(),
			'members' => $this->getMembersArray($t),
			'createdBy' => is_null ( $t->getCreatedBy () ) ? "" : $t->getCreatedBy ()->getFirstname () . " " . $t->getCreatedBy ()->getLastname (),
			'subject' => $t->getSubject (),
			'type' => $t->getType (),
			'estimation' => $t->getEstimation(),
			'_links' => [
				'self' => $this->url->fromRoute('tasks', ['id' => $t->getId()]),  
			],
		];

		return $rv;
	}
	
    private function getMembersArray(Task $task){
		$members = array();
		foreach ($task->getMembers() as $tm) {
			$member = $tm->getMember();
			$members[] = [
	            'firstname' => $member->getFirstname(),
	            'lastname' => $member->getLastname(),
				'role' => $tm->getRole(),
	            'estimation' => $this->getEstimation($tm),
				'_links' => [
					'self' => $this->url->fromRoute('users', ['id' => $member->getId()]),  
				],
			];
		}
		return $members;
    }
    
    private function getEstimation(TaskMember $tm) {
    	$estimation = $tm->getEstimation();
    	return is_null($estimation) ? null : [
			'value' => $estimation->getValue(),
    		'createdAt' => date_format($estimation->getCreatedAt(), 'c'),
    	];
    }
}
