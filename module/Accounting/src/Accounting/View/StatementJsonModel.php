<?php
namespace Accounting\View;

use Accounting\Entity\Withdrawal;
use Zend\Server\Reflection\ReflectionClass;
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
		$rv = [
			'type' => $account instanceof OrganizationAccount ? 'shared' : 'personal',
			'id' => $account->getId(),
			'createdAt' => date_format($account->getCreatedAt(), 'c'),
			'organization' => [
				'id' => $account->getOrganization()->getId(),
				'name' => $account->getOrganization()->getName()
			],
			'holders' => array_column(array_map([$this, 'serializeHolder'], $account->getHolders()), null, 'id'),
			'_embedded' => [
				'transactions' => array_map([$this, 'serializeTransaction'], $transactions)
			],
			'count' => count($transactions),
			'total' => $totalTransactions,
			'_links' => $this->serializeLinks($account)
		];
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
		$rv = [
			'date' => date_format($transaction->getCreatedAt(), 'c'),
			'type' => $this->evaluateTransactionType($transaction),
			'amount' => $transaction->getAmount(),
			'description' => $transaction->getDescription(),
			'balance' => $transaction->getBalance(),
		];
		if($transaction->getPayeeName() != null) {
			$rv['payee'] = $transaction->getPayeeName();
		}
		if($transaction->getPayerName() != null) {
			$rv['payer'] = $transaction->getPayerName();
		}
		return $rv;
	}
	
	private function evaluateTransactionType(Transaction $transaction){
		$account = $this->getVariable('resource');
		if($transaction instanceof Withdrawal || $transaction instanceof Deposit) {
			$reflect = new \ReflectionClass($transaction);
			return $reflect->getShortName();
		}
		if($transaction->getPayer() != null && $transaction->getPayer()->getId() == $account->getId()){
			return 'OutgoingTransfer';
		}else if ($transaction->getPayee() != null && $transaction->getPayee()->getId() == $account->getId()){
			return 'IncomingTransfer';
		}
		return '';
	}

	private function serializeHolder(User $holder) {
		return [
			'id' => $holder->getId(),
			'firstname' => $holder->getFirstname(),
			'lastname' => $holder->getLastname()
		];
	}
}
