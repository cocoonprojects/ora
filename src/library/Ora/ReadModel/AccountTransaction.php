<?php
namespace Ora\ReadModel;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity @ORM\Table(name="account_transactions")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @author andreabandera
 *
 */
class AccountTransaction extends DomainEntity {
	
	/**
	 * @ORM\ManyToOne(targetEntity="Ora\ReadModel\Account", inversedBy="transactions")
	 * @ORM\JoinColumn(name="account_id", referencedColumnName="id", onDelete="CASCADE")
	 * @var Account
	 */
	protected $account;
	/**
	 * 
	 * @var unknown
	 */
	protected $amount;
	
}