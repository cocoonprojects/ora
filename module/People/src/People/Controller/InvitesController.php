<?php

namespace People\Controller;

use ZFX\Rest\Controller\HATEOASRestfulController;
use AcMailer\Service\MailService;
use People\Entity\OrganizationMembership;
use People\Service\OrganizationService;
use Zend\View\Model\JsonModel;

class InvitesController extends HATEOASRestfulController
{
	protected static $collectionOptions = ['GET', 'POST'];
	protected static $resourceOptions = ['GET', 'POST'];

	protected $mailService;
	protected $organizationService;
	protected $host;

	public function __construct(
		OrganizationService $organizationService,
		MailService $mailService
	)
	{
		$this->organizationService = $organizationService;
		$this->mailService = $mailService;
	}

	public function setHost($host) {
		$this->host = $host;
		return $this;
	}

	protected function createInvitation($user, $organization, $data)
	{
		$email = $data['email'];
		$name = $data['name'];

		if ($data['surname']) {
		    $name .= ' ' . $data['surname'];
		}

		$data = new \stdClass();
		$data->guestName = $name;
		$data->guestEmail = $email;
		$data->invitedBy = $user->getFirstname().' '.$user->getLastname();
		$data->orgName = $organization->getName();
		$data->orgId = $organization->getId();
		$data->invitedOn = date('d/m/Y');

		return $data;
	}

	public function invoke($id, $data)
	{
		$user = $this->identity();
		if(is_null($user)) {
			$this->response
				 ->setStatusCode(401);

			return $this->response;
		}

		$organization = $this->organizationService
							 ->getOrganization($this->params('id'));

		if(is_null($organization)) {
			$this->response
			     ->setStatusCode(404);

			return $this->response;
		}

		if (!$user->isOwnerOf($organization) && !$user->isRoleMemberOf($organization)) {

			$this->response
			     ->setStatusCode(403);

			return $this->response;
		}

		$invite = $this->createInvitation($user, $organization, $data);

		$this->mailService
			 ->setSubject("Hey {$data['name']} {$data['surname']}, join {$organization->getName()}");

		$message = $this->mailService
						->getMessage();

		$this->mailService
			 ->setTemplate('mail/invite-to-org.phtml', array(
			 	'invite' => $invite,
			));

		$message->setTo($data['email']);

		$result = $this->mailService->send();

		return new JsonModel(['result' => $result->isValid()]);
	}

	public function get($id)
	{
		$organization = $this->organizationService
							 ->getOrganization($id);

		if(is_null($organization)) {
			$this->response
			     ->setStatusCode(404);

			return $this->response;
		}

		$token = $this->getRequest()
					  ->getQuery("token");

		$data = json_decode(base64_decode($token));

		return new JsonModel(['result' => $data]);
	}

	protected function getCollectionOptions()
	{
		return self::$collectionOptions;
	}

	protected function getResourceOptions()
	{
		return self::$resourceOptions;
	}

	// protected function serializeOne(OrganizationMembership $membership) {
	// 	return [
	// 		'id'        => $membership->getMember()->getId(),
	// 		'firstname' => $membership->getMember()->getFirstname(),
	// 		'lastname'  => $membership->getMember()->getLastname(),
	// 		'email'     => $membership->getMember()->getEmail(),
	// 		'picture'   => $membership->getMember()->getPicture(),
	// 		'role'      => $membership->getRole(),
	// 		'createdAt' => date_format($membership->getCreatedAt(), 'c'),
	// 		'createdBy' => is_null ( $membership->getCreatedBy() ) ? "" : $membership->getCreatedBy ()->getFirstname () . " " . $membership->getCreatedBy ()->getLastname (),
	// 		'_links' => [
	// 			'self' => [
	// 				'href' => $this->url()->fromRoute('members', ['orgId' => $membership->getOrganization()->getId(), 'id' => $membership->getMember()->getId()])
	// 			],
	// 			'ora:account' => [
	// 				'href' => $this->url()->fromRoute('statements', ['orgId' => $membership->getOrganization()->getId(), 'id' => $membership->getMember()->getId(), 'controller' => 'members'])
	// 			],
	// 			'ora:member-stats' => [
	// 				'href' => $this->url()->fromRoute('collaboration', ['orgId' => $membership->getOrganization()->getId(), 'id' => $membership->getMember()->getId(), 'controller' => 'member-stats'])
	// 			]
	// 		]
	// 	];
	// }

}
