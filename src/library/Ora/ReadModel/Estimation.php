<?php

namespace Ora\ReadModel;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Embeddable
 * @author Andrea Lupia
 */

class Estimation {

	CONST NOT_ESTIMATED = -1;
	
	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 * @var DateTime
	 */
	protected $createdAt;
	
	/**
	 *	@ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
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
