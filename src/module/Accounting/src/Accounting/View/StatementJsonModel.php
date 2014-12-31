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
	private $url;
	
	public function __construct(Url $url) {
		$this->url = $url;	
	} 
	
	public function serialize()
	{
		$account = $this->getVariable('resource');
		$representation['balance'] = array(
				'value' => $account->getBalance()->getValue(),
				'date' => date_format($account->getBalance()->getDate(), 'c'),
		);
		foreach ($account->getTransactions() as $transaction) {
			$representation['transactions'][] = $this->serializeOne($transaction);
		}
		$representation['_links']['self'] = $this->url->fromRoute('accounts', ['id' => $account->getId(), 'controller' => 'statements']); 
		if($account instanceof OrganizationAccount) {
			$representation['_links']['deposits'] = $this->url->fromRoute('accounts', ['accountId' => $account->getId(), 'controller' => 'deposits']);
		}
		return Json::encode($representation);
	}
	
	private function serializeOne(AccountTransaction $transaction) {
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
}