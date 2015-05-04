<?php

namespace People\Entity;

use Zend\Permissions\Acl\Resource\ResourceInterface;
use Doctrine\ORM\Mapping AS ORM;
use Application\Entity\EditableEntity;

/**
 * @ORM\Entity @ORM\Table(name="organizations")
 */
class Organization extends EditableEntity implements ResourceInterface
{
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
	private $name;
	
	public function getName()
	{
		return $this->name;
	}
	
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}
	
	public function getResourceId()
	{
		return 'Ora\Organization';
	}
} 