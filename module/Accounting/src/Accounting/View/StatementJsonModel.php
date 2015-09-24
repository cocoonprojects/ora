<?php
namespace Accounting\View;

use Zend\View\Model\JsonModel;
use Zend\Json\Json;
use Zend\Mvc\Controller\Plugin\Url;
use Zend\Permissions\Acl\Acl;
use Application\Entity\User;
use Accounting\Entity\Account;
use Accounting\Entity\OrganizationAccount;
use Accounting\Entity\Transaction;
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
		$transactions = $this->getVariable('transactions');
		$totalTransactions = $this->getVariable('totalTransactions');
		
		$rv['organization'] = $account->getOrganization()->getName();
		$rv['transactions'] = array_map(array($this, 'serializeTransaction'), $transactions);
		$rv['_links']       = $this->serializeLinks($account);
		$rv['count'] 		= count($transactions);
		$rv['total'] 		= $totalTransactions;
		if($rv['count'] < $rv['total']){
			$controller = $account instanceof OrganizationAccount ? 'organization-statement' : 'personal-statement';
			$rv['_links']['next']['href'] = $this->url->fromRoute('statements', ['orgId' => $account->getOrganization()->getId(), 'id' => $account->getId(), 'controller' => $controller]);
		}
		return Json::encode($rv);
	}
	
	protected function serializeLinks($account) {

		$controller = $account instanceof OrganizationAccount ? 'organization-statement' : 'personal-statement';
		$rv['self']['href'] = $this->url->fromRoute('statements', ['orgId' => $account->getOrganization()->getId(), 'id' => $account->getId(), 'controller' => $controller]);
		if($this->acl->isAllowed($this->user, $account, 'Accounting.Account.list')) {
			$rv['ora:account']['href'] = $this->url->fromRoute('accounts', ['orgId' => $account->getOrganization()->getId()]);
		}
		if($this->acl->isAllowed($this->user, $account, 'Accounting.Account.deposit')) {
			$rv['ora:deposit']['href'] = $this->url->fromRoute('accounts', ['orgId' => $account->getOrganization()->getId(), 'id' => $account->getId(), 'controller' => 'deposits']);
		}
		if($this->acl->isAllowed($this->user, $account, 'Accounting.Account.withdrawal')) {
			$rv['ora:withdrawal']['href'] = $this->url->fromRoute('accounts', ['orgId' => $account->getOrganization()->getId(), 'id' => $account->getId(), 'controller' => 'withdrawals']);
		}
		if($this->acl->isAllowed($this->user, $account, 'Accounting.Account.incoming-transfer')) {
			$rv['ora:incoming-transfer']['href'] = $this->url->fromRoute('accounts', ['orgId' => $account->getOrganization()->getId(), 'id' => $account->getId(), 'controller' => 'incoming-transfers']);
		}
		if($this->acl->isAllowed($this->user, $account, 'Accounting.Account.outgoing-transfer')) {
			$rv['ora:outgoing-transfer']['href'] = $this->url->fromRoute('accounts', ['orgId' => $account->getOrganization()->getId(), 'id' => $account->getId(), 'controller' => 'outgoing-transfers']);
		}
		return $rv;
	}
	
	protected function serializeTransaction(Transaction $transaction) {
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
