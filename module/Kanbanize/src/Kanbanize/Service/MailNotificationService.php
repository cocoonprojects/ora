<?php

namespace Kanbanize\Service;

use People\Entity\Organization;
use People\Service\OrganizationService;
use AcMailer\Service\MailServiceInterface;

class MailNotificationService implements NotificationService{

	public function __construct(MailServiceInterface $mailService, OrganizationService $orgService) {
		$this->mailService = $mailService;
		$this->orgService = $orgService;
	}

	/**
	 * (non-PHPdoc)
	 * @see \Kanbanize\Service\NotificationService::sendKanbanizeImportResult()
	 */
	public function sendKanbanizeImportResult($result, Organization $organization){
		$memberships = $this->orgService->findOrganizationMemberships($organization, null, null);
		foreach ($memberships as $m) {
			$recipient = $m->getMember();
			$message = $this->mailService->getMessage();
			$message->setTo($recipient->getEmail());
			$message->setSubject("A new import from Kanbanize as been completed.");
			$this->mailService->setTemplate( 'mail/import-result.phtml', [
					'result' => $result,
					'recipient'=> $recipient,
					'organization'=> $organization
			]);
			$this->mailService->send();
			$rv[] = $recipient;
		}
		return $rv;
	}
}