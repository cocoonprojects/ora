<?php

namespace Ora\Kanbanize;

use \DateTime;
use Doctrine\ORM\Mapping as ORM;
use Ora\EntitySerializer;

/**
 * @author Andrea Lupia <alupia@dimes.unical.it>
 * @ORM\Entity
 *
 */
final class KanbanizeTaskMovedEvent extends KanbanizeEvent {
	
	public function __construct(DateTime $firedAt, KanbanizeTask $task, EntitySerializer $entitySerializer) {
		parent::__construct($firedAt, $task, $entitySerializer);
		
		$this->attributes = $entitySerializer->toJson($task);
	}
}
