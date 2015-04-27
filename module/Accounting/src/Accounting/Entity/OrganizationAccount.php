<?php
namespace Accounting\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Application\Entity\User;
use Application\Entity\Organization;

/**
 * @ORM\Entity
 *
 */
class OrganizationAccount extends Account {
	
	public function __construct($id, Organization $organization) {
		parent::__construct($id);
		$this->organization = $organization;
	}
	
	public function getOrganization() {
		return $this->organization;
	}
	
	public function getName() {
		return $this->getOrganization()->getName();
	}

	public function getResourceId(){
		return "Ora\OrganizationAccount";
	}
}