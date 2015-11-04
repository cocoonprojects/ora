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
	CONST SETTING_KANBANIZE_SUBDOMAIN = "kanbanizeSubdomain";

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

	public function setSetting($key, $value){
		$this->settings[$key] = $settings;
		return $this;
	}

	public function getSetting($key){
		return $this->settings[$key];
	}

	public function getSettings(){
		return $this->settings;
	}

	public function getResourceId()
	{
		return 'Ora\Organization';
	}
} 