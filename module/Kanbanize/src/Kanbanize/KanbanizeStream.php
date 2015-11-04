<?php

namespace Kanbanize;

use TaskManagement\Stream;

class KanbanizeStream extends Stream {

	/**
	 * @var String
	 */
	private $boardId;
	/**
	 * @var String
	 */
	private $projectId;

	public static function create(Organization $organization, User $createdBy, $options){
		if(!isset($options['boardId'])) {
			throw InvalidArgumentException('Cannot create a KanbanizeStream without boardId');
		}
		if(!isset($options['projectId'])) {
			throw InvalidArgumentException('Cannot create a KanbanizeStream without projectId');
		}
		if(!isset($options['projectName'])) {
			throw InvalidArgumentException('Cannot create a KanbanizeStream without projectName');
		}
		if(!isset($options['boardName'])) {
			throw InvalidArgumentException('Cannot create a KanbanizeStream without boardName');
		}
		$rv = new self();
		$rv->recordThat(StreamCreated::occur(Uuid::uuid4()->toString(), [
				'organizationId' => $organization->getId(),
				'by' => $createdBy->getId(),
				'boardId' => $options['boardId'],
				'projectId' => $options['projectId']
		]));

		$rv->setSubject($options['projectName']."\\".$options['boardName'], $createdBy);
		return $rv;
	}
	
	protected function whenStreamCreated(StreamCreated $event) {
		parent::whenStreamCreated($event);
		$this->boardId = $event->payload()['boardId'];
		$this->projectId = $event->payload()['projectId'];
	}
}