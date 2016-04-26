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
	/**
	 * @var String
	 */
	private $lanename;

	public static function create(Stream $stream, $subject, BasicUser $createdBy, array $options = null) {
		if(!isset($options['taskid'])) {
			throw InvalidArgumentException('Cannot create a KanbanizeTask without a taskid option');
		}
		if(!isset($options['columnname'])) {
			throw InvalidArgumentException('Cannot create a KanbanizeTask without a columnname option');
		}
		$rv = new self();
		$rv->id = Uuid::uuid4();
		$rv->status = self::STATUS_IDEA;
		$rv->recordThat(TaskCreated::occur($rv->id->toString(), [
			'status' => $rv->status,
			'taskid' => $options['taskid'],
			'organizationId' => $stream->getOrganizationId(),
			'streamId' => $stream->getId(),
			'by' => $createdBy->getId(),
			'columnname' => $options["columnname"],
			'lanename' => $options["lanename"],
			'subject' => $subject
			'description' =>$options["description"]
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

	public function setLaneName($name, BasicUser $updatedBy){
		if(empty($name)) {
			throw new InvalidArgumentException("Lane name cannot be empty");
		};
		$this->recordThat(TaskUpdated::occur($this->id->toString(), array(
			'lanename' => trim($name),
			'by' => $updatedBy->getId(),
		)));
		return $this;
	}

	public function getLaneName(){
		return $this->lanename;
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return 'kanbanizetask';
	}

	protected function whenTaskCreated(TaskCreated $event) {
		parent::whenTaskCreated($event);

		$this->taskid = $event->payload()['taskid'];
		$this->columnname = $event->payload()['columnname'];
		if (isset($event->payload()['lanename']))
			$this->lanename = $event->payload()['lanename'];
		$this->subject = $event->payload()['subject'];
	}

	protected function whenTaskUpdated(TaskUpdated $event) {
		parent::whenTaskUpdated($event);

		$pl = $event->payload();
		if(isset($pl['columnname'])) {
			$this->columnname = $pl['columnname'];
		}
		if(isset($pl['lanename'])) {
			$this->lanename = $pl['lanename'];
		}
		if(isset($pl['assignee'])) {
			$this->assignee = $pl['assignee'];
		}
	}

	public function getResourceId(){
		return 'Ora\KanbanizeTask';
	}
}