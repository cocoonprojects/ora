<?php

namespace Kanbanize\Entity;

use TaskManagement\Entity\Stream;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="kanbanizestreams")
 */
class KanbanizeStream extends Stream {

	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
	private $boardId;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var String
	 */
	private $projectId;

	public function setBoardId($id){
		$this->boardId = $id;
		return $this;
	}

	public function getBoardId(){
		return $this->boardId;
	}

	public function setProjectId($id){
		$this->projectId = $id;
		return $this;
	}

	public function getProjectId(){
		return $this->projectId;
	}

	public function getType(){
		return 'kanbanizestream';
	}
}