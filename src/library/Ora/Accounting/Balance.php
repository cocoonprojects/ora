<?php
namespace Ora\Accounting;

use Doctrine\ORM\Mapping AS ORM;

/**
 * ORM\Embeddable
 * @author andreabandera
 *
 */
class Balance {
	
	/**
	 * ORM\Column(type="integer")
	 * @var int
	 */
	private $value;
	
	/**
	 * ORM\Column(type="datetime")
	 * @var DateTime
	 */
	private $date;
	
	public function __construct($value, \DateTime $date)
	{
		$this->value = $value;
		$this->date = $date;
	}
	
	public function getValue()
	{
		return $this->value;
	}
	
	public function getDate()
	{
		return $this->date;
	}
}