<?php
namespace Accounting\Entity;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 *
 */
class Transfer extends Transaction
{
	public function __construct(Account $payer, Account $payee, $amount = 0){
		$this->payer = $payer;
		$this->payee = $payee;
		$this->amount = $amount;
	}
}