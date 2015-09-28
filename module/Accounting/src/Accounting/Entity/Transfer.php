<?php
namespace Accounting\Entity;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 *
 */
class Transfer extends Transaction
{
	public function __construct($id, Account $payer, Account $payee){
		parent::__construct($id);
		$this->payer = $payer;
		$this->payee = $payee;
	}
}