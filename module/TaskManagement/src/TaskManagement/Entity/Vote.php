<?php

namespace TaskManagement\Entity;

use Doctrine\ORM\Mapping AS ORM;
/**
 * @ORM\Embeddable
 */
class Vote {
	
	CONST VOTE_FOR = '1';
	CONST VOTE_AGAINST  = '0';
	CONST VOTE_ABSTAIN = '2';
	CONST NO_VOTE = '-1';
	
	/**
	 * @ORM\Column(type="integer", length=1)
	 * @var int 
	 */
	private $value;
	
	/**
	 * @ORM\Column(type="datetime")
	 * @var DateTime
	 */
	private $date;
	
	public function __construct(\DateTime $date){
		$this->value = self::NO_VOTE;
		$this->date = $date;
	}
	
	public function getValue(){
		return $this->value;
	}
	
	public function setValue($val){
		$this->value = $val;
	}
	
	public function getDate(){
		return $this->date;
	}
}