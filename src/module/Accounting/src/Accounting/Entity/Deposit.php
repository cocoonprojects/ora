<?php
namespace Accounting\Entity;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 *
 */
class Deposit extends AccountTransaction
{
	public function getPayerName() {
		return $this->createdBy->getFirstname() . ' ' . $this->createdBy->getLastname();
	}
}