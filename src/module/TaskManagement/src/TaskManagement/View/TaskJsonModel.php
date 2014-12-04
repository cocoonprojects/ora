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
        $count=0;
        $avg="Not enough Estimations";
        $sum=0;
        foreach ($t->getMembers() as $tm) {

            $member = $tm->getMember();

            if($member->getId() === $loggedUser->getId() && $alreadyMember === false){
                $alreadyMember = true;
            }           

            //manage estimation calculation //
            $estimation = $tm->getEstimation();
            if(!is_null($estimation)){
                if($estimation->getValue() > 0){
                    $count++;
                    $sum += $estimation->getValue();
                }
            }
            //end estimation calculation

            $items = $this->getItemsForMembersArray($tm);
            foreach($items as $key=>$val){
                if(!is_null($val)){
                    $memberToAdd[$key] = $val;
                }
            }
            $members[] = $memberToAdd;
        }


        if($count==0)
            $avg="Estimation Not Available";
        else if($count>=2){
            $avg = $sum/$count;
        }
        
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

    private function getItemsForMembersArray(TaskMember $tm){

        $member = $tm->getMember();

        return array(
            'id' => $member->getId(),
            'firstname' => $member->getFirstname(),
            'lastname' => $member->getLastname(),
            'estimationStatus' => (is_null($tm->getEstimation()) ? null : 1)
        );
    }
}
