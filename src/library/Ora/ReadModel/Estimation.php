<?php

namespace Ora\ReadModel;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Embeddable
 */

class Estimation {

	CONST NOT_ESTIMATED = -1;
	
	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 * @var DateTime
	 */
	protected $createdAt;
	
	/**
	 * @ORM\Column(type="float", precision=10, scale=2, nullable=true)
	 * @var float
	 */
	private $value;
	
	public function __construct($value, \DateTime $createdAt) {
		$this->value = $value;
		$this->createdAt = $createdAt;
	}
	
	public function getValue() {
		return $this->value;
	}
	
	public function getCreatedAt() {
		return $this->createdAt;
	}
	
}
