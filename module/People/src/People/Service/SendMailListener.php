<?php

namespace People\Service;

use People\Organization;
use People\OrganizationMemberAdded;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\Event;
use Zend\EventManager\ListenerAggregateInterface;
use TaskManagement\Task;
use AcMailer\Service\MailService;
use Application\Service\UserService;
use Application\Entity\User;
use Zend\Mvc\Application;

class SendMailListener implements ListenerAggregateInterface
{
	/**
	 * @var MailService
	 */
	private $mailService;
	/**
	 * @var UserService
	 */
	private $userService;
	/**
	 * @var OrganizationService
	 */
	private $organizationService;

	protected $listeners = array ();

	public function __construct(MailService $mailService, UserService $userService, OrganizationService $organizationService) {
		$this->mailService = $mailService;
		$this->userService = $userService;
		$this->organizationService = $organizationService;
	}

	public function attach(EventManagerInterface $events) {
		$this->listeners [] = $events->getSharedManager ()->attach (Application::class, OrganizationMemberAdded::class, function (Event $event) {
			$streamEvent = $event->getTarget();
			$organizationId = $streamEvent->metadata()['aggregate_id'];
			$organization = $this->organizationService->getOrganization($organizationId);
			$memberId = $event->getParam ( 'userId' );
			$member = $this->userService->findUser($memberId);
			$this->sendMemberAddedInfoMail ( $organization, $member );
		} );
	}

	public function detach(EventManagerInterface $events) {
		foreach ( $this->listeners as $index => $listener ) {
			if ($events->detach ( $listener )) {
				unset ( $this->listeners [$index] );
			}
		}
	}

	/**
	 * @param Organization $organization
	 * @param User $member
	 * @throws \AcMailer\Exception\MailException
	 */
	public function sendMemberAddedInfoMail(Organization $organization, User $member)
	{
		$this->mailService->setSubject ( 'A new member joined "' . $organization->getName() . '"');
		$message = $this->mailService->getMessage();

		$admins = $organization->getAdmins();
		foreach ($admins as $id => $profile) {
			if($id == $member->getId()) {
				continue;
			}
			$recipient = $this->userService->findUser($id);
			$this->mailService->setTemplate( 'mail/new-member-info.phtml', array(
				'recipient' => $recipient,
				'member'=> $member,
				'organization'=> $organization
			));
			$message->setTo($recipient->getEmail());
			$result = $this->mailService->send();
		}
	}
}