<?php

namespace Ora\Kanbanize;

use Doctrine\ORM\Mapping AS ORM;
use Ora\StreamManagement\Stream;
use Ora\User\User;
use Rhumsaa\Uuid\Uuid;
use Ora\ReadModel\Task;


/**
 * @author Andrea Lupia <alupia@dimes.unical.it>
 * @ORM\Entity
 *
 *
 */
class KanbanizeTask extends Task {

	//FIXME how to map backlog?
	CONST BACKLOG = 'Backlog';
	CONST COLUMN_IDEA = 'Idea';
	CONST COLUMN_OPEN = 'Open';
	CONST COLUMN_ONGOING = "OnGoing";
	CONST COLUMN_COMPLETED = 'Completed';
	CONST COLUMN_ACCEPTED = 'Accepted';

    CONST TYPE = 'kanbanizetask';

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
	
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	private $boardId;

	/**
	 *  @ORM\Column(type="string")
	 * @var string
	 */
	private $taskId;

	public static function create(Stream $stream, $subject, User $createdBy) {
		$rv = new self();
		$rv->id = Uuid::uuid4();
		$rv->status = self::STATUS_ONGOING;
		$rv->subject = $subject;
		$rv->recordThat(TaskCreated::occur($rv->id->toString(), array(
				'task' => $rv,
				'createdBy' => $createdBy,
		)));
		$rv->changeStream($stream, $createdBy);
		$rv->addMember($createdBy, $createdBy);
		return $rv;
	}
	
	public function getBoardId() {
		return $this->boardId;
	}
	
	public function setBoardId($boardid){
		$this->boardId=$boardid;
	}
	
	public function getTaskId() {
		return $this->taskId;
	}
	
	public static function getMappedStatus($status) {
		return self::$mapping[$status];
	}
	
	
	/* 
	public function setBoardId($boardId) {
		$this->boardId = $boardId;
	}
	
	public function setTaskId($taskId) {
		$this->taskId = $taskId;
	} */
	
}

?>
