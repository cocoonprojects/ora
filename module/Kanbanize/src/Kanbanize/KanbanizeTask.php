<?php
namespace Kanbanize;

use Application\Entity\BasicUser;
use TaskManagement\Stream;
use TaskManagement\Task;
use TaskManagement\TaskCreated;

class KanbanizeTask extends Task {
	
	//FIXME how to map backlog?
	CONST BACKLOG = 'Backlog';
	CONST COLUMN_IDEA = 'Idea';
	CONST COLUMN_OPEN = 'Open';
	CONST COLUMN_ONGOING = "OnGoing";
	CONST COLUMN_COMPLETED = 'Completed';
	CONST COLUMN_ACCEPTED = 'Accepted';

	private static $mapping = array(
			self::COLUMN_IDEA		=> Task::STATUS_IDEA,
			self::COLUMN_OPEN		=> Task::STATUS_OPEN,
			self::COLUMN_ONGOING	=> Task::STATUS_ONGOING,
			self::COLUMN_COMPLETED	=> Task::STATUS_COMPLETED,
			self::COLUMN_ACCEPTED	=> Task::STATUS_ACCEPTED,
			Task::STATUS_IDEA		=> self::COLUMN_IDEA, 
			Task::STATUS_OPEN		=> self::COLUMN_OPEN,
			Task::STATUS_ONGOING	=> self::COLUMN_ONGOING,
			Task::STATUS_COMPLETED	=> self::COLUMN_COMPLETED,
			Task::STATUS_ACCEPTED	=> self::COLUMN_ACCEPTED,
			//FIXME backlog?
			self::BACKLOG			=> -1,
			-1						=> self::BACKLOG
	);
	
	private $kanbanizeBoardId;
	
	private $kanbanizeTaskId;
	
	public static function create(Stream $stream, $subject, BasicUser $createdBy, array $options = null) {
		if(!isset($options['kanbanizeBoardId']) || !isset($options['kanbanizeTaskId'])) {
			throw InvalidArgumentException('Cannot create a KanbanizeTask without a kanbanizeBoardId or kanbanizeTaskId option');
		}
		$rv = new self();
		$rv->id = Uuid::uuid4();
		$rv->status = self::STATUS_ONGOING;
		$rv->recordThat(TaskCreated::occur($rv->id->toString(), [
			'status' => $rv->status,
			'kanbanizeBoadId' => $options['kanbanizeBoardId'],
			'kanbanizeTaskId' => $options['kanbanizeTaskId'],
			'organizationId' => $stream->getOrganizationId(),
			'streamId' => $stream->getId(),
			'by' => $createdBy->getId(),
		]));
		$rv->setSubject($subject, $createdBy);
		return $rv;
	}
	
	public function getKanbanizeBoardId() {
		return $this->kanbanizeBoardId;
	}
	
	public function getKanbanizeTaskId() {
		return $this->kanbanizeTaskId;
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return 'kanbanizetask';
	}

	public static function getMappedStatus($status) {
		return self::$mapping[$status];
	}
	
	protected function whenTaskCreated(TaskCreated $event) {
		parent::whenTaskCreated($event);
		$this->kanbanizeBoardId = $event->payload()['kanbanizeBoardId'];
		$this->kanbanizeTaskId = $event->payload()['kanbanizeTaskId'];
	}
}