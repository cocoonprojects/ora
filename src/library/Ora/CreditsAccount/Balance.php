<?php
namespace Ora\CreditsAccount;

class Balance {

	private $value;
	
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