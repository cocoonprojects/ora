<?php
namespace Kanbanize;

use Application\Entity\BasicUser;
use Application\IllegalStateException;
use TaskManagement\Task;
use TaskManagement\TaskCreated;
use TaskManagement\TaskOngoing;
use TaskManagement\TaskCompleted;
use TaskManagement\TaskClosed;
use TaskManagement\TaskAccepted;
use TaskManagement\TaskUpdated;
use TaksManagement\TaskMoved;
use Rhumsaa\Uuid\Uuid;
use TaskManagement\Stream;

class KanbanizeTask extends Task {
	
	//FIXME how to map backlog?
// 	CONST BACKLOG = 'Backlog';
// 	CONST COLUMN_IDEA = 'Idea';
// 	CONST COLUMN_OPEN = 'Open';
// 	CONST COLUMN_ONGOING = "OnGoing";
// 	CONST COLUMN_COMPLETED = 'Completed';
// 	CONST COLUMN_ACCEPTED = 'Accepted';
// 	CONST COLUMN_CLOSED = 'Closed';

	CONST EMPTY_ASSIGNEE = 'None';

	//Mapping fo kanbanize columns is not static: is setted for each organization
// 	private static $mapping = array(
// 			self::COLUMN_IDEA		=> Task::STATUS_IDEA,
// 			self::COLUMN_OPEN		=> Task::STATUS_OPEN,
// 			self::COLUMN_ONGOING	=> Task::STATUS_ONGOING,
// 			self::COLUMN_COMPLETED	=> Task::STATUS_COMPLETED,
// 			self::COLUMN_ACCEPTED	=> Task::STATUS_ACCEPTED,
// 			self::COLUMN_CLOSED		=> Task::STATUS_CLOSED,
// 			Task::STATUS_IDEA		=> self::COLUMN_IDEA, 
// 			Task::STATUS_OPEN		=> self::COLUMN_OPEN,
// 			Task::STATUS_ONGOING	=> self::COLUMN_ONGOING,
// 			Task::STATUS_COMPLETED	=> self::COLUMN_COMPLETED,
// 			Task::STATUS_ACCEPTED	=> self::COLUMN_ACCEPTED,
// 			Task::STATUS_CLOSED		=> self::COLUMN_CLOSED,
// 			//FIXME backlog?
// 			self::BACKLOG			=> -1,
// 			-1						=> self::BACKLOG
// 	);

	/**
	 * @var String
	 */
	private $taskid;
	/**
	 * @var String
	 */
	private $assignee;
	/**
	 * @var String
	 */
	private $columnname;
	
	public static function create(Stream $stream, $subject, BasicUser $createdBy, array $options = null) {
		if(!isset($options['taskid'])) {
			throw InvalidArgumentException('Cannot create a KanbanizeTask without a taskid option');
		}
		if(!isset($options['columnname'])) {
			throw InvalidArgumentException('Cannot create a KanbanizeTask without a columnname option');
		}
		$rv = new self();
		$rv->id = Uuid::uuid4();
		$rv->status = $options["status"];
		$rv->recordThat(TaskCreated::occur($rv->id->toString(), [
			'status' => $rv->status,
			'taskid' => $options['taskid'],
			'organizationId' => $stream->getOrganizationId(),
			'streamId' => $stream->getId(),
			'by' => $createdBy->getId(),
			'columnname' => $options["columnname"]
		]));
		$rv->setSubject($subject, $createdBy);
		return $rv;
	}
	
	public function getKanbanizeTaskId() {
		return $this->taskId;
	}

	public function setAssignee($assignee, BasicUser $updatedBy){
		$assignee = is_null($assignee) ? self::EMPTY_ASSIGNEE : trim($assignee);
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
			'columnname' => trim($name),
			'by' => $updatedBy->getId(),
		)));
		return $this;
	}

	public function getColumnName(){
		return $this->columnname;
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return 'kanbanizetask';
	}

	public static function isEmptyAssignee($assignee){
		return $assignee == self::EMPTY_ASSIGNEE;
	}

	protected function whenTaskCreated(TaskCreated $event) {
		parent::whenTaskCreated($event);

		$this->taskid = $event->payload()['taskid'];
		$this->columnname = $event->payload()['columnname'];
	}

	protected function whenTaskUpdated(TaskUpdated $event) {
		parent::whenTaskUpdated($event);

		$pl = $event->payload();
		if(array_key_exists('columnname', $pl)) {
			$this->columnname = $pl['columnname'];
		}
		if(array_key_exists('assignee', $pl)) {
			$this->assignee = $pl['assignee'];
		}
	}

	public function getResourceId(){
		$ids = self::RESOURCE_IDS;
		return $ids[1];
	}
}