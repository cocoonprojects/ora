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

	/**
	 * @return string
	 */
	public function getBoardId() {
		return $this->boardId;
	}

	/**
	 * @param $boardid string
	 */
	public function setBoardId($boardid){
		$this->boardId=$boardid;
	}

	/**
	 * @return string
	 */
	public function getTaskId() {
		return $this->taskId;
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return 'kanbanizetask';
	}

}