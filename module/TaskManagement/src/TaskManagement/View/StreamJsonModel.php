<?php
namespace TaskManagement\View;

use Zend\View\Model\JsonModel;
use Zend\Json\Json;
use Zend\Mvc\Controller\Plugin\Url;
use Application\Entity\User;
use People\Entity\Organization;
use TaskManagement\Entity\Stream;

class StreamJsonModel extends JsonModel
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
	/**
	 *
	 * @var Organization
	 */
	private $organization;
	
	public function __construct(Url $url, User $user, Organization $organization) {
		$this->url = $url;
		$this->user = $user;
		$this->organization = $organization;
	}
	
	public function serialize()
	{
		$resource = $this->getVariable('resource');
		if(is_array($resource)) {
			$hal['_links']['self']['href'] = $this->url->fromRoute('streams', ['orgId'=>$this->organization->getId()]);
			$hal['_embedded']['ora:stream'] = array_column(array_map(array($this, 'serializeOne'), $resource), null, 'id');
			$hal['count'] = count($resource);
			$hal['total'] = count($resource);
		} else {
			$hal = $this->serializeOne($resource);
		}
		return Json::encode($hal);
	}

	protected function serializeOne(Stream $stream) {
		$rv = [
			'id' => $stream->getId (),
			'subject' => $stream->getSubject (),
			'createdAt' => date_format($stream->getCreatedAt(), 'c'),
			'createdBy' => is_null ( $stream->getCreatedBy () ) ? "" : $stream->getCreatedBy ()->getFirstname () . " " . $stream->getCreatedBy ()->getLastname (),
			'_links' => [
				'self' => $this->url->fromRoute('streams', ['id' => $stream->getId(), 'orgId'=>$this->organization->getId()]),
			],
		];

		return $rv;
	}
}