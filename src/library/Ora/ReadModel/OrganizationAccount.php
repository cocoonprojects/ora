<?php
namespace Ora\ReadModel;

use Doctrine\ORM\Mapping AS ORM;
use Ora\User\User;
use Ora\ReadModel\Account;

/**
 * @ORM\Entity
 * @author andreabandera
 *
 */
class OrganizationAccount extends Account {
	
	/**
	 * @ORM\OneToOne(targetEntity="Organization")
	 * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", nullable=true)
	 * @var Organization
	 */
	private $organization;
	
	public function __construct($id, User $user, Organization $organization) {
		parent::__construct($id, $holder);
		$this->organization = $organization;
	}
	
	public function getOrganization() {
		return $this->organization;
	}
	
}