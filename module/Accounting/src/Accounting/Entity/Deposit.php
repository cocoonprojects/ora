<?php
namespace Accounting\Entity;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 *
 */
class Deposit extends Transaction
{
	public function __construct(Account $payee){
		$this->payee = $payee;
	}

	public function getPayerName() {
		return $this->createdBy->getFirstname() . ' ' . $this->createdBy->getLastname();
	}
}