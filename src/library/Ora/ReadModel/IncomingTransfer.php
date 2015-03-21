<?php
namespace Ora\ReadModel;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @author andreabandera
 *
 */
class IncomingTransfer extends AccountTransaction
{
	/**
	 * @ORM\ManyToOne(targetEntity="Ora\ReadModel\Account")
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
}