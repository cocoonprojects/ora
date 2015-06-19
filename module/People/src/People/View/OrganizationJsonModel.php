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
			'createdAt' => date_format($organization->getCreatedAt(), 'c'),
			'createdBy' => is_null ( $organization->getCreatedBy () ) ? "" : $organization->getCreatedBy ()->getFirstname () . " " . $organization->getCreatedBy ()->getLastname (),
			'_links' => [
				'self' => [
					'href' => $this->url->fromRoute('organizations', ['orgId' => $organization->getId()])
				],
			]
		];

		return $rv;
	}
}