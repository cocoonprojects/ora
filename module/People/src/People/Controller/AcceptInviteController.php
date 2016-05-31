<?php

namespace People\Controller;

use ZFX\Rest\Controller\HATEOASRestfulController;
use AcMailer\Service\MailService;
use People\Entity\OrganizationMembership;
use People\Service\OrganizationService;
use Zend\View\Model\JsonModel;

class AcceptInviteController extends HATEOASRestfulController
{
	protected static $collectionOptions = ['GET'];
	protected static $resourceOptions = ['GET'];

	protected $organizationService;

	public function __construct(OrganizationService $organizationService)
	{
		$this->organizationService = $organizationService;
	}

	public function getList()
	{
		$token = $this->getRequest()
					  ->getQuery("token");

		$data = json_decode(base64_decode($token));

		if (!isset($data->orgId)) {
			$this->response
			     ->setStatusCode(400);

			return $this->response;
		}

		$organization = $this->organizationService
							 ->getOrganization($data->orgId);

		if(is_null($organization)) {
			$this->response
			     ->setStatusCode(404);

			return $this->response;
		}

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
