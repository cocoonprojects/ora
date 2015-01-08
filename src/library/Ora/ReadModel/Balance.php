<?php
namespace Ora\ReadModel;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Embeddable
 * @author andreabandera
 *
 */
class Balance {
	
	/**
	 * @ORM\Column(type="float", scale=2)
	 * @var float
	 */
	private $value;
	
	/**
	 * @ORM\Column(type="datetime")
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