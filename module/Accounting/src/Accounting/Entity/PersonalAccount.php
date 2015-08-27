<?php
namespace Accounting\Entity;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 *
 */
class PersonalAccount extends Account
{
	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function getResourceId(){
		return 'Ora\PersonalAccount';
	}
}