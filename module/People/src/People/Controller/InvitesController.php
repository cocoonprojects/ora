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

		if (isset($data['surname'])) {
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
		$hash = base64_encode(json_encode($invite));

		$link = $this->url()
					 ->fromRoute('invites',
					 	[],
					 	['query' => ['token' => $hash],
		                             'force_canonical' => true]
        );

		if (getenv('APPLICATION_ENV') == 'production') {
			$p = parse_url($link);
			$link = "{$p['scheme']}://{$p['host']}/index.html#{$p['path']}?{$p['query']}";
		}

		$this->mailService
			 ->setSubject("Hey {$data['name']} {$data['surname']}, join {$organization->getName()}");

		$message = $this->mailService
						->getMessage();

		$this->mailService
			 ->setTemplate('mail/invite-to-org.phtml', array(
			 	'invite' => $invite,
			 	'link' => $link,
			));

		$message->setTo($data['email']);

		$result = $this->mailService->send();

		return new JsonModel(['result' => $result->isValid()]);
	}

	protected function getCollectionOptions()
	{
		return self::$collectionOptions;
	}

	protected function getResourceOptions()
	{
		return self::$resourceOptions;
	}

}