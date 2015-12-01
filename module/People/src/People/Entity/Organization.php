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
	
	/**
	 * @ORM\Column(type="json_array", nullable=true)
	 * @var string
	 */
	private $settings;

	public function getName()
	{
		return $this->name;
	}
	
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	public function setSetting($settingKey, $settingValue){
		$this->settings[$key] = $value;
		return $this;
	}

	public function getSetting($key){
		if(array_key_exists($key, $this->settings)){
			return $this->settings[$key];
		}
		return null;
	}

	public function getSettings(){
		return $this->settings;
	}

	public function getResourceId()
	{
		return 'Ora\Organization';
	}
} 