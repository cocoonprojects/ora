<?php

namespace People\View;

use Zend\View\Model\JsonModel;
use Zend\Json\Json;

class TaskStatsJsonModel extends JsonModel{

	public function serialize(){

		$taskMemberInClosedTasks = $this->getVariable('taskMemberInClosedTasks');

		$creditsCount = 0;
		$averageDelta = null;
		if(!empty($taskMemberInClosedTasks)){
			$deltaSharesCount = 0;
			$deltaSharesSum = 0;
			foreach ($taskMemberInClosedTasks as $member){
				$creditsCount += $member->getCredits();
				if(!is_null($member->getDelta())){
					$deltaSharesSum += $member->getDelta();
					$deltaSharesCount++;
				}
			}
			$averageDelta = $deltaSharesCount > 0 ? $deltaSharesSum / $deltaSharesCount : null;
		}

		$hal['_embedded']['ora:task']['ownershipsCount'] = $this->getVariable('ownershipsCount');
		$hal['_embedded']['ora:task']['creditsCount'] = $creditsCount;
		$hal['_embedded']['ora:task']['averageDelta'] = $averageDelta;

		return Json::encode($hal);
	}
}