<?php

namespace Ora;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\MappedSuperclass
 * @author andreabandera
 *
 */
class DomainEntity {
	
	/**
	 * @ORM\Id @ORM\Column(type="string") 
	 * @var string
	 */
	private $id;
	
	/**
	 * @ORM\Column(type="datetime")
	 * @var DateTime
	 */
	private $createdAt;
		
	protected function __construct($id, \DateTime $createdAt) 
	{
		$this->setId($id);
		$this->setCreatedAt($createdAt);
	}
	
	public function getId() 
	{
		return $this->id;
	}
	
	public function setId($id)
	{
	    $this->id = $id;
	}
	
	public function getCreatedAt() 
	{
		return $this->createdAt;
	}
	
	public function setCreatedAt($createdAt)
	{
	    $this->createdAt = $createdAt;
	}
	
	public function rebuild($events) 
	{
		foreach ($events as $event)
		{
			$this->apply($event);
		}
	}
	
	private function apply(DomainEvent $domainEvent) 
	{
		$method = 'apply'.get_class($domainEvent);
		$this->$method($domainEvent);
	}
}