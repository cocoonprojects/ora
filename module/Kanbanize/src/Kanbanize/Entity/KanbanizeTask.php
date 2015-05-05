<?php
namespace Kanbanize\Entity;

use Doctrine\ORM\Mapping AS ORM;
use TaskManagement\Entity\Task;

/**
 * @ORM\Entity
 */
class KanbanizeTask extends Task
{
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

	public function getBoardId() {
		return $this->boardId;
	}
	
	public function setBoardId($boardid){
		$this->boardId=$boardid;
	}
	
	public function getTaskId() {
		return $this->taskId;
	}
}