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
	
	
	/* 
	public function setBoardId($boardId) {
		$this->boardId = $boardId;
	}
	
	public function setTaskId($taskId) {
		$this->taskId = $taskId;
	} */
	
}

?>