<?php
namespace Accounting\Entity;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 *
 */
class Withdrawal extends AccountTransaction
{
	public function getPayeeName() {
		return $this->createdBy->getFirstname() . ' ' . $this->createdBy->getLastname();
	}
}