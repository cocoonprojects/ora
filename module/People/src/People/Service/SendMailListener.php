<?php

namespace People\Service;

use People\Organization;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\Event;
use Zend\EventManager\ListenerAggregateInterface;
use TaskManagement\Task;
use AcMailer\Service\MailService;
use Application\Service\UserService;
use Application\Entity\User;

class SendMailListener implements ListenerAggregateInterface
{
	private $mailService;
	private $userService;

	protected $listeners = array ();

	public function __construct(MailService $mailService, UserService $userService) {
		$this->mailService = $mailService;
		$this->userService = $userService;
	}

	public function attach(EventManagerInterface $events) {
		$that = $this;
		$this->listeners [] = $events->getSharedManager ()->attach ( 'People\OrganizationService', Organization::EVENT_MEMBER_ADDED, function (Event $event) use ($that) {
			$organization = $event->getTarget ();
			$member = $event->getParam ( 'by' );
			$that->sendMemberAddedInfoMail ( $organization, $member );
		} );
	}

	public function detach(EventManagerInterface $events) {
		foreach ( $this->listeners as $index => $listener ) {
			if ($events->detach ( $listener )) {
				unset ( $this->listeners [$index] );
			}
		}
	}

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