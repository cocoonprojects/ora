<?php
namespace Kanbanize\Entity;

use Doctrine\ORM\Mapping AS ORM;
use TaskManagement\Entity\Task;

/**
 * @ORM\Entity
 * @ORM\Table(name="kanbanizetasks")
 */
class KanbanizeTask extends Task
{
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
	private $taskId;

	/**
	 *  @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
	private $columnName;

	/**
	 *  @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
	private $assignee;
	/**
	 * @param string $taskId
	 */
	public function setTaskId($taskId){
		$this->taskId = $taskId;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getTaskId() {
		return $this->taskId;
	}

	/**
	 * @param string $columnName
	 */
	public function setColumnName($columnName){
		$this->columnName = $columnName;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getColumnName() {
		return $this->columnName;
	}

	/**
	 * @param string $assignee
	 */
	public function setAssignee($assignee){
		$this->assignee = $assignee;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getAssignee() {
		return $this->assignee;
	}

	/**
	 * @return string
	 */
	public function getType(){
		return 'kanbanizetask';
	}

	public function getResourceId(){
		return 'Ora\KanbanizeTask';
	}
}