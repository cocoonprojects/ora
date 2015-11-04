<?php
namespace Accounting\Entity;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 *
 */
class Withdrawal extends Transaction
{
	public function __construct(Account $payer, $amount = 0){
		$this->payer = $payer;
		$this->amount = $amount;
	}

	public function getPayeeName() {
		return $this->createdBy->getFirstname() . ' ' . $this->createdBy->getLastname();
	}
}