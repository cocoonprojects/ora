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

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function getResourceId(){
		return 'Ora\OrganizationAccount';
	}
}