<?php

namespace People;


class MissingOrganizationMembershipException extends \DomainException {

	public function __construct($organizationId, $userId)
	{
		parent::__construct('User '.$userId.' is not member of organization '.$organizationId);
	}
}