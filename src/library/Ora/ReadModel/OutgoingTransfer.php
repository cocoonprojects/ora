<?php
namespace Ora\ReadModel;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @author andreabandera
 *
 */
class OutgoingTransfer extends AccountTransaction
{
	/**
	 * @ORM\ManyToOne(targetEntity="Ora\ReadModel\Account")
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
}