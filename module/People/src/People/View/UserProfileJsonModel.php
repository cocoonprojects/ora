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
		
		$rv = [
				'OrgId' => $organization->getId(),
				'OrgName'=> $organization->getName(),
				'MemberRole'=>$role,
				'UserId' => $user->getId(),
				'Firstname' => $user->getFirstname(),
				'Lastname'=> $user->getLastname(),
				'Email'=> $user->getEmail(),
				'Avatar'=> $user->getPicture(),
		];
		return Json::encode($rv);
	}	
	
}