<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity @ORM\Table(name="organizations")
 */
class Organization extends EditableEntity
{
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
	private $name;
	
	public function getName() {
		return $this->name;
	}
	
	public function setName($name) {
		$this->name = $name;
		return $this;
	}
} 