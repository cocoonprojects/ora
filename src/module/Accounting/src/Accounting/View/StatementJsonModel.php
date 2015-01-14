<?php
namespace Accounting\View;

use Zend\View\Model\JsonModel;
use Zend\Json\Json;
use Ora\ReadModel\Account;
use Ora\ReadModel\OrganizationAccount;
use Zend\Mvc\Controller\Plugin\Url;
use Ora\ReadModel\AccountTransaction;

class StatementJsonModel extends JsonModel
{
	protected $url;
	
	public function __construct(Url $url) {
		$this->url = $url;	
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
		if($this->isAllowed('deposit', $account)) {
			$rv['_links']['deposits'] = $this->url->fromRoute('accounts', ['id' => $account->getId(), 'controller' => 'deposits']);
		}
		return $rv;
	}
	
	private function serializeTransaction(AccountTransaction $transaction) {
		$className = get_class($transaction);
		$rv = array(
			'date' => date_format($transaction->getCreatedAt(), 'c'),
			'payer' => $transaction->getCreatedBy()->getFirstname() . ' '. $transaction->getCreatedBy()->getLastname(),
			'type' => substr($className, strrpos($className, '\\') + 1),
			'amount' => $transaction->getAmount(),
			'description' => $transaction->getDescription(),
			'balance' => $transaction->getBalance(),
		);
		return $rv;
	}
	
	protected function isAllowed($action, Account $account = null) {
    	if(is_null($account)) {
    		return true; // placeholder
    	}
    	switch ($action) {
    		case 'statement':
    			return true;
    		case 'deposit':
    			if($account instanceof OrganizationAccount) {
    				return true;
    			}
    			return false;
    		default:
    			return false;
    	}
	}
}