<?php
namespace Application\View;

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
		$resource = $this->getVariable('resource');
		if(is_array($resource)) {
			$hal['count'] = count($resource);
			$hal['total'] = count($resource);
			$hal['_embedded']['ora:organization-membership'] = array_map(array($this, 'serializeOne'), $resource);
			$hal['_links'] = [
				'self' => [
					'href' => $this->url->fromRoute('memberships'),
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
			'organization' => $this->serializeOrganization($membership->getOrganization()),
			'role' => $membership->getRole(),
			'createdAt' => date_format($membership->getCreatedAt(), 'c'),
			'createdBy' => is_null ( $membership->getCreatedBy () ) ? "" : $membership->getCreatedBy ()->getFirstname () . " " . $membership->getCreatedBy ()->getLastname (),
		];

		return $rv;
	}
	
	private function serializeOrganization(Organization $org) {
		return [
			'id' => $org->getId(),
			'name' => $org->getName(),
			'_links' => [
				'self' => [
					'href' => $this->url->fromRoute('organizations', ['id' => $org->getId()]),
				],
				'ora:organization-member' => [
					'href' => $this->url->fromRoute('organizations-entities', ['orgId' => $org->getId(), 'controller' => 'members'])
				]
			]
		];
	}
}