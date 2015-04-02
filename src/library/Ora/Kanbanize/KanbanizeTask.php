<?php
namespace Ora\Kanbanize;

use Ora\InvalidArgumentException;
use Ora\User\User;
use TaskManagement\Task;
use TaskManagement\Stream;
use TaskManagement\TaskCreated;

class KanbanizeTask extends Task {
	
	private $kanbanizeBoardId;
	
	private $kanbanizeTaskId;
	
	public static function create(Stream $stream, $subject, User $createdBy, array $options = null) {
		if(!isset($options['kanbanizeBoardId']) || !isset($options['kanbanizeTaskId'])) {
			throw InvalidArgumentException('Cannot create a KanbanizeTask without a kanbanizeBoardId or kanbanizeTaskId option');
		}
		$rv = new self();
		$rv->id = Uuid::uuid4();
		$rv->status = self::STATUS_ONGOING;
		$rv->recordThat(TaskCreated::occur($rv->id->toString(), array(
				'status' => $rv->status,
				'kanbanizeBoadId' => $options['kanbanizeBoardId'],
				'kanbanizeTaskId' => $options['kanbanizeTaskId'],
				'by' => $createdBy->getId(),
		)));
		$rv->setSubject($subject, $createdBy);
		$rv->changeStream($stream, $createdBy);
		$rv->addMember($createdBy, $createdBy, self::ROLE_OWNER);
		return $rv;
	}
	
	public function getKanbanizeBoardId() {
		return $this->kanbanizeBoardId;
	}
	
	public function getKanbanizeTaskId() {
		return $this->kanbanizeTaskId;
	}
	
	protected function whenTaskCreated(TaskCreated $event) {
		parent::whenTaskCreated($event);
		$this->kanbanizeBoardId = $event->payload()['kanbanizeBoardId'];
		$this->kanbanizeTaskId = $event->payload()['kanbanizeTaskId'];
	}
}