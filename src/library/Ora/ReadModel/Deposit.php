<?php
namespace Ora\ReadModel;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @author andreabandera
 *
 */
class Deposit extends AccountTransaction
{
	public function getPayerName() {
		return $this->createdBy->getFirstname() . ' ' . $this->createdBy->getLastname();
	}
}