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
		$totalCredits = $this->getVariable('total-gen-credits');
		$last3MonthsCredits = $this->getVariable('last-3-month');
		$last6MonthsCredits = $this->getVariable('last-6-month');
		$lastYearCredits = $this->getVariable('last-year');
		
		$rv = [			
				'id'=>$user->getId(),
				'firstname' => $user->getFirstname(),
				'lastname'=> $user->getLastname(),
				'picture'=> $user->getPicture(),
				'email'=> $user->getEmail(),
				'_embedded'=>[
						'organization'=>[
								'id'=>$organization->getId(),
								'name'=>$organization->getName(),
								'role'=>$role
						],
						'credits'=>[
								'balance'=>$balance,
								'total'=>$totalCredits,
								'last3M'=>$last3MonthsCredits,
								'last6M'=>$last6MonthsCredits,
								'lastY'=>$lastYearCredits
						],
				],
		];
		return Json::encode($rv);
	}	
	
}
