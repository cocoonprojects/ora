<?php

namespace Ora\Kanbanize;

use Doctrine\ORM\Mapping as ORM;
use Ora\DomainEvent;
use \DateTime;
use Ora\EntitySerializer;

/**
 * @author Andrea Lupia <alupia@dimes.unical.it>
 * @ORM\Entity
 * 
 */
class KanbanizeEvent extends DomainEvent {
	
	protected function __construct(DateTime $firedAt, KanbanizeTask $task, EntitySerializer $entitySerializer) {
		parent::__construct($firedAt);
	}
	
}
