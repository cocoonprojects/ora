<?php
namespace Accounting\Entity;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 *
 */
class Transfer extends Transaction
{
	public function __construct(Account $payer, Account $payee){
		$this->payer = $payer;
		$this->payee = $payee;
	}
}