<?php
namespace People\View;

use Zend\View\Model\JsonModel;
use Zend\Json\Json;
use Zend\Mvc\Controller\Plugin\Url;
use Application\Entity\User;
use People\Entity\Organization;

class OrganizationJsonModel extends JsonModel
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
	private $identity;
	
	public function __construct(Url $url, User $identity) {
		$this->url = $url;
		$this->identity = $identity;
	}
	
	public function serialize()
	{
		$resource = $this->getVariable('resource');
		if(is_array($resource)) {
			$hal['count'] = count($resource);
			$hal['total'] = count($resource);
			$hal['_embedded']['ora:organization'] = array_map(array($this, 'serializeOne'), $resource);
			$hal['_links'] = [
				'self' => [
					'href' => $this->url->fromRoute('organizations'),
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

	protected function serializeOne(Organization $organization) {
		$rv = [
			'id' => $organization->getId(),
			'name' => $organization->getName(),
			'membership' => $this->identity->isMemberOf($organization),
			'createdAt' => date_format($organization->getCreatedAt(), 'c'),
			'createdBy' => is_null ( $organization->getCreatedBy () ) ? "" : $organization->getCreatedBy ()->getFirstname () . " " . $organization->getCreatedBy ()->getLastname (),
			'_links' => [
				'self' => [
					'href' => $this->url->fromRoute('organizations', ['orgId' => $organization->getId()])
				],
				'ora:member' => [
					'href' => $this->url->fromRoute('organizations-entities', ['orgId' => $organization->getId(), 'controller' => 'members'])
				]
			]
		];

		return $rv;
	}
}