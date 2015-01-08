<?php
namespace Ora\Accounting;

use Rhumsaa\Uuid\Uuid;
use Ora\Organization\Organization;
use Ora\User\User;

class OrganizationAccount extends Account {
	
	private $organizationId;
	
	public static function create(User $holder) {
		throw new \Exception('Unsupported creation method. Use \'createOrganizationAccount\'');
	}
	
	public static function createOrganizationAccount(User $holder, Organization $organization) {
		$rv = new self();
		$rv->id = Uuid::uuid4();		
		$rv->holders[] = $holder->getId();
		// At creation time the balance is 0
		$rv->recordThat(AccountCreated::occur($rv->id->toString(), array(
				'balance' => 0,
				'holders' => $rv->holders,
				'organization' => $organization->getId(),
		)));
		return $rv;
	} 
	

	protected function whenAccountCreated(AccountCreated $event) {
		parent::whenAccountCreated($event);
		$this->organizationId = $event->payload()['organization'];
	}
	
}