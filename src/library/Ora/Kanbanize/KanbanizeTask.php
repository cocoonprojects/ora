<?php

namespace Ora\Kanbanize;

use Ora\TaskManagement\Task;
use Doctrine\ORM\Mapping AS ORM;


/**
 * @author Andrea Lupia <alupia@dimes.unical.it>
 * @ORM\Entity @ORM\Table(name="kanbanize_tasks")
 *
 *
 */
class KanbanizeTask  {
	
	
	/** @ORM\Id @ORM\OneToOne(targetEntity="Ora\TaskManagement\Task") */
	private $task;
	

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
	
	
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	private $kanbanizeTitle;
	
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	private $kanbanizeStatus;
	
	
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
	
	public function getTask(){
		return $this->task;
	}
	
	public function setTask($task){
		$this->task=$task;
	}
	
	public function getStatus(){
		return $this->kanbanizeStatus;
	}
	
	public function setStatus ($status){
		$this->kanbanizeStatus=$status;
	}
	
}

?>