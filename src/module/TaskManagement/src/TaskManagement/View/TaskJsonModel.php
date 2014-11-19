<?php
namespace TaskManagement\View;

use Zend\View\Model\JsonModel;
use Zend\Json\Json;
use Ora\ReadModel\Task;
use Ora\User\User;

class TaskJsonModel extends JsonModel
{
	public function serialize()
	{
		$resource = $this->getVariable('resource');
		$url = $this->getVariable('url');
        $loggedUser = $this->getVariable('user');

        if(is_array($resource)) {
			$representation = array( 'tasks' => array());
			foreach ($resource as $r) {
				$representation['tasks'][] = $this->serializeOne($r, $url, $loggedUser);
			}
		} else {
			$representation = $this->serializeOne($resource, $url, $loggedUser);
		}
		return Json::encode($representation);
	}
	
    private function serializeOne(Task $t, $url, User $loggedUser) {

        $members = array();
        $alreadyMember = false;
		foreach ($t->getMembers() as $m) {
			$members[] = array(
					'id' => $m->getId(),
					'firstname' => $m->getFirstname(),
					'lastname' => $m->getLastname(),
                );
            if($m->getId() === $loggedUser->getId() && $alreadyMember === false){
                $alreadyMember = true;
            }
		}
		$rv = array(
			'id' => $t->getId(),
			'createdAt' => $t->getCreatedAt(),
		    'status' => $t->getStatus(),
            'members' => $members,
            'createdBy' => is_null($t->getCreatedBy()) ? "" :  $t->getCreatedBy()->getFirstname()." ".$t->getCreatedBy()->getLastname(),
            'subject' => $t->getSubject(),
            'type' => $t->getType(),
            'alreadyMember' => $alreadyMember
		);
		if(!is_null($url)) {
			$rv['_links'] = array('self' => $url.'/'.$t->getId()); 
		}
		return $rv;
	}
}
