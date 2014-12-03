<?php
namespace TaskManagement\View;

use Zend\View\Model\JsonModel;
use Zend\Json\Json;
use Ora\ReadModel\Task;
use Ora\User\User;
use Ora\ReadModel\Estimation;
use Ora\ReadModel\TaskMember;

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
        foreach ($t->getMembers() as $tm) {

            $member = $tm->getMember();
			$members[] = array(
					'id' => $member->getId(),
					'firstname' => $member->getFirstname(),
					'lastname' => $member->getLastname(),
                );
                   
            if($member->getId() === $loggedUser->getId() && $alreadyMember === false){
                $alreadyMember = true;
            }            
		}
			
			// manage estimation calculation //
			$count=0;
			$avg="Not enough Estimations";
			$sum=0;
	foreach($t->getMembers() as  $member){
		$estimation = $member->getEstimation();
		if (!is_null($estimation)){
			if($estimation->getValue()>0){
			$count++;
			$sum += $estimation->getValue();
			}
		}
		
		
	}
	if($count==0)
		$avg="Estimation Not Available";
	else if($count>=2){
		$avg = $sum/$count;}
		// end estimation calculation
		
		
		$rv = array(
			'id' => $t->getId(),
			'createdAt' => $t->getCreatedAt(),
		    'status' => $t->getStatus(),
            'members' => $members,
            'createdBy' => is_null($t->getCreatedBy()) ? "" :  $t->getCreatedBy()->getFirstname()." ".$t->getCreatedBy()->getLastname(),
            'subject' => $t->getSubject(),
            'type' => $t->getType(),
            'alreadyMember' => $alreadyMember,
				'estimation'=>$avg
		);
		if(!is_null($url)) {
			$rv['_links'] = array('self' => $url.'/'.$t->getId()); 
		}
		return $rv;
	}
}
