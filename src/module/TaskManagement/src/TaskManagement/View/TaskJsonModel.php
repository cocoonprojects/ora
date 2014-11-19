<?php
namespace TaskManagement\View;

use Zend\View\Model\JsonModel;
use Zend\Json\Json;
use Ora\ReadModel\Task;

class TaskJsonModel extends JsonModel
{
	public function serialize()
	{
		$resource = $this->getVariable('resource');
		$url = $this->getVariable('url');
		if(is_array($resource)) {
			$representation = array( 'tasks' => array());
			foreach ($resource as $r) {
				$representation['tasks'][] = $this->serializeOne($r, $url);
			}
		} else {
			$representation = $this->serializeOne($resource, $url);
		}
		return Json::encode($representation);
	}
	
	private function serializeOne(Task $t, $url) {
		$members = array();
		foreach ($t->getMembers() as $m) {
			$members[] = array(
					'id' => $m->getId(),
					'firstname' => $m->getFirstname(),
					'lastname' => $m->getLastname(),
			);
		}
		$rv = array(
			'id' => $t->getId(),
			'createdAt' => $t->getCreatedAt(),
			'status' => $t->getStatus(),
			'members' => $members,
		);
		if(!is_null($url)) {
			$rv['_links'] = array('self' => $url.'/'.$t->getId()); 
		}
		return $rv;
	}
}