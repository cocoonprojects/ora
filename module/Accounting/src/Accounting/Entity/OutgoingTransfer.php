<?php
namespace Accounting\Entity;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 *
 */
class OutgoingTransfer extends AccountTransaction
{
	/**
	 * @ORM\ManyToOne(targetEntity="Account")
	 * @ORM\JoinColumn(name="payee_id", referencedColumnName="id", onDelete="SET NULL")
	 * @var Account
	 */
	private $payee;
	
	public function setPayee(Account $payee) {
		$this->payee = $payee;
		return $this;
	}
	
	public function getPayee() {
		return $this->payee;
	}

	public function getPayeeName() {
		return $this->payee->getName();
	}
}