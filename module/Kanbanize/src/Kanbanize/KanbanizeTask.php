<?php
namespace Kanbanize;

use Application\Entity\BasicUser;
use TaskManagement\Stream;
use TaskManagement\Task;
use TaskManagement\TaskCreated;
use TaskManagement\TaskOngoing;
use TaskManagement\TaskCompleted;
use TaskManagement\TaskClosed;
use TaskManagement\TaskAccepted;
use TaskManagement\TaskUpdated;

class KanbanizeTask extends Task {
	
	//FIXME how to map backlog?
	CONST BACKLOG = 'Backlog';
	CONST COLUMN_IDEA = 'Idea';
	CONST COLUMN_OPEN = 'Open';
	CONST COLUMN_ONGOING = "OnGoing";
	CONST COLUMN_COMPLETED = 'Completed';
	CONST COLUMN_ACCEPTED = 'Accepted';
	CONST COLUMN_CLOSED = 'Closed';

	private static $mapping = array(
			self::COLUMN_IDEA		=> Task::STATUS_IDEA,
			self::COLUMN_OPEN		=> Task::STATUS_OPEN,
			self::COLUMN_ONGOING	=> Task::STATUS_ONGOING,
			self::COLUMN_COMPLETED	=> Task::STATUS_COMPLETED,
			self::COLUMN_ACCEPTED	=> Task::STATUS_ACCEPTED,
			self::COLUMN_CLOSED		=> Task::STATUS_CLOSED,
			Task::STATUS_IDEA		=> self::COLUMN_IDEA, 
			Task::STATUS_OPEN		=> self::COLUMN_OPEN,
			Task::STATUS_ONGOING	=> self::COLUMN_ONGOING,
			Task::STATUS_COMPLETED	=> self::COLUMN_COMPLETED,
			Task::STATUS_ACCEPTED	=> self::COLUMN_ACCEPTED,
			Task::STATUS_CLOSED		=> self::COLUMN_CLOSED,
			//FIXME backlog?
			self::BACKLOG			=> -1,
			-1						=> self::BACKLOG
	);

	/**
	 * @var String
	 */
	private $taskId;
	/**
	 * @var String
	 */
	private $assignee;
	/**
	 * @var String
	 */
	private $columnName;
	
	public static function create(Stream $stream, $subject, BasicUser $createdBy, array $options = null) {
		if(!isset($options['taskId'])) {
			throw InvalidArgumentException('Cannot create a KanbanizeTask without a taskId option');
		}
		if(!isset($options['columnName'])) {
			throw InvalidArgumentException('Cannot create a KanbanizeTask without a columnName option');
		}
		$rv = new self();
		$rv->id = Uuid::uuid4();
		$rv->status = array_key_exists($options["columnName"], self::$mapping) ? self::$mapping[$options["columnName"]] : null;
		$rv->recordThat(TaskCreated::occur($rv->id->toString(), [
			'status' => $rv->status,
			'taskId' => $options['taskId'],
			'organizationId' => $stream->getOrganizationId(),
			'streamId' => $stream->getId(),
			'by' => $createdBy->getId(),
			'assignee' => array_key_exists($options["assignee"], $options) ? $options["assignee"] : null,
			'columnName' => $options["columnName"],
		]));
		$rv->setSubject($subject, $createdBy);
		return $rv;
	}
	
	public function getKanbanizeTaskId() {
		return $this->taskId;
	}

	public function setAssignee($assignee, BasicUser $updatedBy){
		$assignee = is_null($assignee) ? null : trim($assignee);
		$this->recordThat(TaskUpdated::occur($this->id->toString(), array(
			'assignee' => $assignee,
			'by' => $updatedBy->getId(),
		)));
		return $this;
	}

	public function getAssignee(){
		return $this->assignee;
	}

	public function setColumnName($name, BasicUser $updatedBy){
		if(empty($name)) {
			throw new InvalidArgumentException("Column name cannot be empty");
		};
		$this->recordThat(TaskUpdated::occur($this->id->toString(), array(
				'columnName' => trim($name),
				'by' => $updatedBy->getId(),
		)));
		return $this;
	}

	public function getColumnName(){
		return $this->columnName;
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
		$this->taskId = $event->payload()['taskId'];
		$this->columnName = $event->payload()['columnName'];
		$this->assignee = $event->payload()['assignee'];
	}

	protected function whenTaskUpdated(TaskUpdated $event) {
		parent::whenTaskUpdated($event);

		$pl = $event->payload();
		if(array_key_exists('columnName', $pl)) {
			$this->columnName = $pl['columnName'];
		}
		if(array_key_exists('assignee', $pl)) {
			$this->assignee = $pl['assignee'];
		}
	}
}