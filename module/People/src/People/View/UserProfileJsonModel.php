<?php
namespace People\View;

use Zend\Json\Json;
use People\Entity\Organization;
use Application\Entity\User;
use Zend\View\Model\JsonModel;

class UserProfileJsonModel extends JsonModel
{
	
	public function serialize()
	{
		
		$organization = $this->getVariable('org-resource');
		$user = $this->getVariable('user-resource');
		$role = $this->getVariable('role-resource');
		$balance = $this->getVariable('account-balance');
		$incomingCredits = $this->getVariable('total-gen-credits');
		$last3MonthCredits = $this->getVariable('last-3-month');
		$last6MonthCredits = $this->getVariable('last-6-month');
		$restOfYearCredits = $this->getVariable('rest-of-year');
		
		$rv = [
				'OrgId' => $organization->getId(),
				'OrgName'=> $organization->getName(),
				'MemberRole'=>$role,
				'UserId' => $user->getId(),
				'Firstname' => $user->getFirstname(),
				'Lastname'=> $user->getLastname(),
				'Email'=> $user->getEmail(),
				'Avatar'=> $user->getPicture(),
				'ActualBalance'=>$balance,
				'TotGenCredits'=>$incomingCredits,
				'Last3MonthCredits'=>$last3MonthCredits,
				'Last6MonthCredits'=>$last6MonthCredits,
				'RestOfTheYearCredits'=>$restOfYearCredits,
		];
		return Json::encode($rv);
	}	
	
}