<?php
namespace Accounting\View;

use Zend\View\Model\JsonModel;
use Zend\Json\Json;
use Zend\Mvc\Controller\Plugin\Url;
use Zend\Permissions\Acl\Acl;
use Application\Entity\User;
use Accounting\Entity\Account;
use Accounting\Entity\OrganizationAccount;
use Accounting\Entity\AccountTransaction;
use Accounting\Entity\Deposit;

class StatementJsonModel extends JsonModel
{
	protected $url;
	
	protected $user;
	
	/**
	 * @var Acl
	 */
	protected $acl;
	
	public function __construct(Url $url, User $user, Acl $acl) {
		$this->url = $url;
		$this->user = $user;
		$this->acl = $acl;
	} 
	
	public function serialize()
	{
		$account = $this->getVariable('resource');
		$rv['balance']		= $this->serializeBalance($account);
		$rv['transactions']	= array_map(array($this, 'serializeTransaction'), $account->getTransactions());
		$rv['_links']		= $this->serializeLinks($account);
		if($account instanceof OrganizationAccount) {
			$rv['organization'] = $account->getOrganization()->getName();
		}
		return Json::encode($rv);
	}
	
	protected function serializeBalance($account) {
		return array(
			'value' => $account->getBalance()->getValue(),
			'date' => date_format($account->getBalance()->getDate(), 'c'),
		);
	}
	
	protected function serializeLinks($account) {
		
		$rv['self'] = $this->url->fromRoute('accounts', ['orgId' => $account->getOrganization()->getId(), 'id' => $account->getId(), 'controller' => 'statement']);
		if($this->acl->isAllowed($this->user, $account, 'Accounting.Account.deposit')){
			$rv['ora:deposit']['href'] = $this->url->fromRoute('accounts', ['orgId' => $account->getOrganization()->getId(), 'id' => $account->getId(), 'controller' => 'deposits']);
		}
		return $rv;
	}
	
	protected function serializeTransaction(AccountTransaction $transaction) {
		$className = get_class($transaction);
		$rv = array(
			'date' => date_format($transaction->getCreatedAt(), 'c'),
			'type' => substr($className, strrpos($className, '\\') + 1),
			'amount' => $transaction->getAmount(),
			'description' => $transaction->getDescription(),
			'balance' => $transaction->getBalance(),
		);
		if($transaction->getPayeeName() != null) {
			$rv['payee'] = $transaction->getPayeeName();
		}
		if($transaction->getPayerName() != null) {
			$rv['payer'] = $transaction->getPayerName();
		}
		return $rv;
	}
}
