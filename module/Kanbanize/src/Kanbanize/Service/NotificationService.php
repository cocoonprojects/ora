<?php

namespace Kanbanize\Service;

use People\Entity\Organization;

interface NotificationService {
	
	/**
	 * Send import details from Kanbanize to all members of an organization
	 * @param array $result
	 * @param Organization $organization
	 * @return BasicUser[] receivers
	 */
	public function sendKanbanizeImportResult($result, Organization $organization);
}