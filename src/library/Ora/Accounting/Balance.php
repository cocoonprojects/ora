<?php
namespace Ora\Accounting;

/**
 * 
 * @author andreabandera
 *
 */
class Balance {
	
	/**
	 * 
	 * @var float
	 */
	private $value;
	
	/**
	 * 
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