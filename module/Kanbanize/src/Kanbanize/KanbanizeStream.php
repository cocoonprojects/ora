<?php

namespace Kanbanize;

use Application\Entity\User;
use Application\InvalidArgumentException;
use People\Organization;
use Rhumsaa\Uuid\Uuid;
use TaskManagement\Stream;
use TaskManagement\StreamCreated;
use TaskManagement\StreamUpdated;



class KanbanizeStream extends Stream {

	/**
	 * @var String
	 */
	private $boardId;
	/**
	 * @var String
	 */
	private $projectId;

	public static function create(Organization $organization, $subject, User $createdBy, $options = []){
		if(!isset($options['boardId'])) {
			throw InvalidArgumentException('Cannot create a KanbanizeStream without boardId');
		}
		if(!isset($options['projectId'])) {
			throw InvalidArgumentException('Cannot create a KanbanizeStream without projectId');
		}
		$rv = new self();
		$rv->recordThat(StreamCreated::occur(Uuid::uuid4()->toString(), [
				'organizationId' => $organization->getId(),
				'by' => $createdBy->getId(),
				'boardId' => $options['boardId'],
				'projectId' => $options['projectId']
		]));

		$rv->setSubject($subject, $createdBy);
		return $rv;
	}
	
	public function getBoardId() {
		return $this->boardId;
	}
	
	public function changeBoardId($boardId, User $by) {
 		if(!isset($boardId)) {
 			throw InvalidArgumentException('Cannot modify\ a KanbanizeStream without boardId');
 		}
 
 		$this->recordThat(StreamUpdated::occur(Uuid::uuid4()->toString(), [
 				'boardId' => $boardId,
 				'by' => $by
 		]));
 	}

	protected function whenStreamCreated(StreamCreated $event) {
		parent::whenStreamCreated($event);
		$this->boardId = $event->payload()['boardId'];
		$this->projectId = $event->payload()['projectId'];
	}
	
	protected function whenStreamUpdated(StreamUpdated $event) {
		parent::whenStreamUpdated($event);
		if (isset($event->payload()['boardId'])) {
			$this->boardId = $event->payload()['boardId'];
		}
	}
}