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

	CONST EMPTY_ASSIGNEE = 'None';

	/**
	 * @var String
	 */
	private $taskid;
	/**
	 * @var String
	 */
	private $assignee = self::EMPTY_ASSIGNEE;
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
		if(!isset($options['status'])) {
			throw InvalidArgumentException('Cannot create a KanbanizeTask without a status option');
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
			'columnname' => $options["columnname"],
			'subject' => $subject
		]));
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
		$this->subject = $event->payload()['subject'];
	}

	protected function whenTaskUpdated(TaskUpdated $event) {
		parent::whenTaskUpdated($event);

		$pl = $event->payload();
		if(isset($pl['columnname'])) {
			$this->columnname = $pl['columnname'];
		}
		if(isset($pl['assignee'])) {
			$this->assignee = $pl['assignee'];
		}
	}

	public function getResourceId(){
		return 'Ora\KanbanizeTask';
	}
}