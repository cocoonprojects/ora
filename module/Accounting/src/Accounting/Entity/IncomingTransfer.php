<?php
namespace Accounting\Entity;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 *
 */
class IncomingTransfer extends AccountTransaction
{
	/**
	 * @ORM\ManyToOne(targetEntity="Account")
	 * @ORM\JoinColumn(name="payer_id", referencedColumnName="id", onDelete="SET NULL")
	 * @var Account
	 */
	private $payer;
	
	public function setPayer(Account $payer) {
		$this->payer = $payer;
		return $this;
	}
	
	public function getPayer() {
		return $this->payer;
	}

	public function getPayerName() {
		return $this->payer->getName();
	}
}