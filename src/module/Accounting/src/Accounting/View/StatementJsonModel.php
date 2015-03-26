<?php
namespace Accounting\View;

use Zend\View\Model\JsonModel;
use Zend\Json\Json;
use Ora\ReadModel\Account;
use Ora\ReadModel\OrganizationAccount;
use Zend\Mvc\Controller\Plugin\Url;
use Ora\ReadModel\AccountTransaction;
use Ora\User\User;
use Ora\ReadModel\Deposit;
use BjyAuthorize\Service\Authorize;

class StatementJsonModel extends JsonModel
{
	protected $url;
	
	protected $user;
	
	/**
	 * @var BjyAuthorize\Service\Authorize
	 */
	protected $authorize;
	
	public function __construct(Url $url, User $user, Authorize $authorize) {
		$this->url = $url;
		$this->user = $user;
		$this->authorize = $authorize;
	} 
	
	public function serialize()
	{
		$account = $this->getVariable('resource');
		$rv = $this->serializeBalance($account);
		$rv = array_merge($rv,
					$this->serializeTransactions($account),
					$this->serializeLinks($account));
		if($account instanceof OrganizationAccount) {
			$rv['organization'] = $account->getOrganization()->getName();
		}
		return Json::encode($rv);
	}
	
	protected function serializeBalance($account) {
		$rv['balance'] = array(
			'value' => $account->getBalance()->getValue(),
			'date' => date_format($account->getBalance()->getDate(), 'c'),
		);
		return $rv;
	}
	
	protected function serializeTransactions($account) {
		$rv['transactions'] = array();
		foreach ($account->getTransactions() as $transaction) {
			$rv['transactions'][] = $this->serializeTransaction($transaction);
		}
		return $rv;
	}
	
	protected function serializeLinks($account) {
		$rv['_links']['self'] = $this->url->fromRoute('accounts', ['id' => $account->getId(), 'controller' => 'statement']);
		if($this->authorize->isAllowed($account, 'Accounting.Account.deposit')){
			$rv['_links']['deposits'] = $this->url->fromRoute('accounts', ['id' => $account->getId(), 'controller' => 'deposits']);
		}
		return $rv;
	}
	
	private function serializeTransaction(AccountTransaction $transaction) {
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