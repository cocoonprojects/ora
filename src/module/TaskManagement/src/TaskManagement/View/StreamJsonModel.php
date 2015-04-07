<?php
namespace TaskManagement\View;

use Zend\View\Model\JsonModel;
use Zend\Json\Json;
use Zend\Mvc\Controller\Plugin\Url;
use Application\Entity\User;
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
	
	public function __construct(Url $url, User $user) {
		$this->url = $url;
		$this->user = $user;
	}
	
	public function serialize()
	{
		$resource = $this->getVariable('resource');
		if(is_array($resource)) {
			$hal['_embedded']['ora:stream'] = array_map(array($this, 'serializeOne'), $resource);
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
				'self' => $this->url->fromRoute('streams', ['id' => $stream->getId()]),
			],
		];

		return $rv;
	}
}