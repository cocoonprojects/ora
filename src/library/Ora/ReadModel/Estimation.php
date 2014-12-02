<?php

namespace Ora\ReadModel;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Ora\ReadModel\Task;
use Ora\User\User;
//use Ora\DomainEntity;

/**
 * @ORM\Entity @ORM\Table(name="estimation")
 * @author Andrea Lupia
 */

class Estimation extends DomainEntity{
    /** @ORM\Id @ORM\ManyToOne(targetEntity="Ora\ReadModel\Task") */
    private $item;
    /** @ORM\Id @ORM\ManyToOne(targetEntity="Ora\User\User") */
    private $user;
    //TODO parte intera?
    /** @ORM\Column(type="decimal", precision=4, scale=2) */
    private $value;

    public function __construct(Task $item, User $user, $value)
    {
    	$this->item = $item;
    	$this->user = $user;
    	$this->value = $value;
    }
    
    public function getItem() {
    	return $this->item;
    }
    
    public function setItem(Task $item) {
    	$this->item = $item;
    }
    
    public function getUser() {
    	return $this>user;
    }
    
    public function setUser(User $user) {
    	$this->user = $user;
    }
    
    public function getValue() {
    	return $this->value;
    }
    
    public function setValue($value) {
    	$this->value = $value;
    }
    
}
