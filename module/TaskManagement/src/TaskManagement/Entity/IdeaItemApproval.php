<?php

namespace TaskManagement\Entity;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 *
 */
class IdeaItemApproval extends Approval {
	
	public function __construct(Vote $vote, \DateTime $createdAt) {
		$this->vote = $vote;
		$this->createdAt = $createdAt;
	}
}