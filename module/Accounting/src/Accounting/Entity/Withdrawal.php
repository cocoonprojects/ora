<?php
namespace Accounting\Entity;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 *
 */
class Withdrawal extends Transaction
{
	public function __construct(Account $payer){
		$this->payer = $payer;
	}

	public function getPayeeName() {
		return $this->createdBy->getFirstname() . ' ' . $this->createdBy->getLastname();
	}
}