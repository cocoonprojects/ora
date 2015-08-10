<?php
namespace Accounting\Entity;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 *
 */
class OrganizationAccount extends Account {
	
	public function getName() {
		return $this->getOrganization()->getName();
	}

	public function getResourceId(){
		return 'Ora\OrganizationAccount';
	}
}