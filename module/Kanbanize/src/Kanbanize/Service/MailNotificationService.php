<?php

namespace Kanbanize\Service;

use People\Entity\Organization;
use People\Service\OrganizationService;
use AcMailer\Service\MailServiceInterface;
use People\Entity\OrganizationMembership;

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

	public function sendKanbanizeSyncAlert(Organization $org)
	{
		$adminsMembers = $this->orgService
			 		   		  ->findOrganizationMemberships(
					 		   		$org,
					 		   		null,
					 		   		null,
			 				   		[OrganizationMembership::ROLE_ADMIN]);

		foreach ($adminsMembers as $adminsMember)
		{
			$admin = $adminsMember->getMember();

			$message = $this->mailService->getMessage();
			$message->setTo($admin->getEmail());
			$message->setSubject("Your connected Kanbanize board is out of sync");

			$this->mailService->setTemplate('mail/board-out-of-sync.phtml', [
					'recipient'=> $admin,
					'organization'=> $org
			]);

			$this->mailService->send();
		}
	}
}