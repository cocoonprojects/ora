<?php

namespace Ora\Kanbanize;

use Ora\TaskManagement\Task;
use Doctrine\ORM\Mapping AS ORM;


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
	 * @ORM\Column(type="integer")
	 * @var int
	 */
	private $boardId;

	/**
	 *  @ORM\Column(type="integer")
	 * @var int
	 */
	private $taskId;

	public function __construct($taskId, $boardId, $kanbanizeTaskId, \DateTime $createdAt, $createdBy) {
		parent::__construct($taskId, $createdAt, $createdBy);
		$this->boardId = $boardId;
		$this->taskId = $kanbanizeTaskId;
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