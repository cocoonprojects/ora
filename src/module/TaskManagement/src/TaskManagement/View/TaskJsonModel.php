<?php
namespace TaskManagement\View;

use Zend\View\Model\JsonModel;
use Zend\Json\Json;
use Ora\ReadModel\Task;
use Ora\User\User;
use Ora\ReadModel\Estimation;

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
		$avg = 0;
		$count = 0;
		$sum = 0;
		if (count ( $t->getEstimations () ) == 0) {
			$avg = "No estimation";
		} else {
			foreach ( $t->getEstimations () as $estimation ) {
				if ($estimation->getValue () > 0) {
					$sum += $estimation->getValue ();
					$count ++;
				}
			}
			if ($count >= 2)
				$avg = $sum / $count;
			else
				$avg = "not enough estimations";
		}
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
