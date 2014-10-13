<?php

namespace Ora\Kanbanize;

use Ora\TaskManagement\Task;

/**
 * @author Andrea Lupia <alupia@dimes.unical.it>
 *
 */
class KanbanizeTask extends Task {

	/**
	 * @ORM\Column(type="integer")
	 * @var int
	 */
	private $boardId;

	/**
	 * @ORM\Column(type="integer")
	 * @var int
	 */
	private $taskId;
	
	
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	private $kanbanizeTitle;
	
	
	public function getKanbanizeTitle() {
		return $this->kanbanizeTitle;
	}
	
	public function setKanbanizeTitle($title) {
		$this->kanbanizeTitle = $title;
	}
	

	public function getBoardId() {
		return $this->boardId;
	}
	
	public function setBoardId($boardId) {
		$this->boardId = $boardId;
	}
	
	public function getTaskId() {
		return $this->taskId;
	}
	
	public function setTaskId($taskId) {
		$this->taskId = $taskId;
	}
	
}

?>