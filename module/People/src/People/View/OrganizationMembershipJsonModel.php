<?php
namespace People\View;

use Zend\View\Model\JsonModel;
use Zend\Json\Json;
use Zend\Mvc\Controller\Plugin\Url;
use Application\Entity\User;
use People\Entity\OrganizationMembership;
use People\Entity\Organization;

class OrganizationMembershipJsonModel extends JsonModel
{
	/**
	 * 
	 * @var Url
	 */
	private $url;
	/**
	 * 
	 * @var User
	 */
	private $user;
	
	public function __construct(Url $url, User $user) {
		$this->url = $url;
		$this->user = $user;
	}
	
	public function serialize()
	{
		$organization = $this->getVariable('organization');
		$resource = $this->getVariable('resource');
		if(is_array($resource)) {
			$hal['count'] = count($resource);
			$hal['total'] = count($resource);
			$hal['_embedded']['ora:organization-member'] = array_column(array_map(array($this, 'serializeOne'), $resource), null, 'id');
			$hal['_links'] = [
				'self' => [
					'href' => $this->url->fromRoute('organizations', ['controller' => 'members', 'orgId' => $organization->getId()]),
				],
// TODO: Introduce 'first' and 'last' only when pagination is implemented
// 				'first' => [
// 					'href' => $this->url->fromRoute('organization-membership'),
// 				],
// 				'last' => [
// 					'href' => $this->url->fromRoute('organization-membership'),
// 				]
			];
		} else {
			$hal = $this->serializeOne($resource);
		}
		return Json::encode($hal);
	}

	protected function serializeOne(OrganizationMembership $membership) {
		$rv = [
			'id' => $membership->getMember()->getId(),
			'firstname' => $membership->getMember()->getFirstname(),
			'lastname' => $membership->getMember()->getLastname(),
			'picture' => $membership->getMember()->getPicture(),
			'role' => $membership->getRole(),
			'createdAt' => date_format($membership->getCreatedAt(), 'c'),
			'createdBy' => is_null ( $membership->getCreatedBy () ) ? "" : $membership->getCreatedBy ()->getFirstname () . " " . $membership->getCreatedBy ()->getLastname (),
			'_links' => [
				'self' => [
					'href' => $this->url->fromRoute('organizations', ['orgId' => $membership->getOrganization()->getId(), 'controller' => 'members', 'id' => $membership->getMember()->getId()])
				],
// 				'ora:user' => [
// 					'href' => $this->url->fromRoute('users', ['id' => $membership->getMember()->getId()])
// 				]
			]
		];

		return $rv;
	}
}