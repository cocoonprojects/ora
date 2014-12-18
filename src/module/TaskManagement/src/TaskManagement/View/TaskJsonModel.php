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
		$members = array ();
		$alreadyMember = false;
		foreach ( $t->getMembers () as $tm ) {

            $memberToAdd = array();    
		    $items = $this->getItemsForMembersArray($tm);
            foreach($items as $key=>$val){
                if(!is_null($val)){
                    $memberToAdd[$key] = $val;
                }
            }
            $members[] = $memberToAdd;

            $member = $tm->getMember ();
            		
			if ($member->getId () === $loggedUser->getId () && $alreadyMember === false) {
				$alreadyMember = true;
			}
		}

		$avg = "N.A.";
		$ans = $this->average($t->getEstimations());
		// manage estimation calculation //
		if (count ($t->getEstimations())==count ($t->getMembers())){
			//every one choose to do not estimate the card
			if ($ans['count'] == 0){
				$avg ="-";
			}
			else {
				$avg = $ans['average'];
			}
			
			
		}else{
			if ($ans['count'] > 1) {
				$avg = $ans['average'];
			}
		}
		// end estimation calculation
		
		$rv = array (
				'id' => $t->getId (),
				'createdAt' => $t->getCreatedAt (),
				'status' => $t->getStatus (),
				'members' => $members,
				'createdBy' => is_null ( $t->getCreatedBy () ) ? "" : $t->getCreatedBy ()->getFirstname () . " " . $t->getCreatedBy ()->getLastname (),
				'subject' => $t->getSubject (),
				'type' => $t->getType (),
				'alreadyMember' => $alreadyMember,
				'estimation' => $avg 
		);
		if (! is_null ( $url )) {
			$rv ['_links'] = array (
					'self' => $url . '/' . $t->getId () 
			);
        }

       

		return $rv;
	}
	
	private function average($estimations){
		$count = 0;
		$sum = 0;
		foreach ( $estimations  as $estimation ) {
			if ($estimation->getValue () != Estimation::NOT_ESTIMATED) {
				$count++;
				$sum += $estimation->getValue ();
			}
	}
	$ans = array();
	$ans['count'] = $count;
	if ($count!=0)
		$ans['average'] = $sum / $count;
	return $ans;

    }

    private function getItemsForMembersArray(TaskMember $tm){

        $member = $tm->getMember();
		
        $estimationObject = $tm->getEstimation();
      
        $estimationValue =  (is_null($estimationObject) ? null : $estimationObject->getValue());

        return array(
            'id' => $member->getId(),
            'firstname' => $member->getFirstname(),
            'lastname' => $member->getLastname(),
            'estimation' => $estimationValue
        );
    }    
}
