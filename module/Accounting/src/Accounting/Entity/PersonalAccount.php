<?php
namespace Accounting\Entity;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 *
 */
class PersonalAccount extends Account
{
	public function getResourceId(){
		return 'Ora\PersonalAccount';
	}
}