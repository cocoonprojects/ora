<?php
namespace Ora\Accounting;

use Rhumsaa\Uuid\Uuid;
use Ora\Organization\Organization;
use Ora\User\User;

class OrganizationAccount extends Account {
	
	private $organizationId;
	
	public static function create(User $createdBy) {
		throw new \Exception('Unsupported creation method. Use \'createOrganizationAccount\'');
	}
	
	public static function createOrganizationAccount(Organization $organization, User $createdBy) {
		$rv = new self();
		// At creation time the balance is 0
		$rv->recordThat(AccountCreated::occur(Uuid::uuid4()->toString(), array(
				'balance' => 0,
				'by' => $createdBy->getId(),
				'organization' => $organization->getId(),
		)));
		$rv->addHolder($createdBy, $createdBy);
		$organization->changeAccount($rv, $createdBy);
		return $rv;
	}
	
	public function getOrganizationId() {
		return $this->organizationId;
	}

	protected function whenAccountCreated(AccountCreated $event) {
		parent::whenAccountCreated($event);
		$this->organizationId = $event->payload()['organization'];
	}
	
}