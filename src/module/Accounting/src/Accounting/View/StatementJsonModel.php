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
		$representation['statement'] = ['balance' => array(
				'value' => $account->getBalance()->getValue(),
				'date' => date_format($account->getBalance()->getDate(), 'c'),
		)];
		foreach ($account->getTransactions() as $transaction) {
			$representation['statement']['transactions'][] = $this->serializeOne($transaction);
		}
		$representation['statement']['_links']['self'] = $this->url->fromRoute('statements', ['id' => $account->getId()]); 
		if($account instanceof OrganizationAccount) {
			$representation['statement']['_links']['deposits'] = $this->url->fromRoute('deposits', ['accountId' => $account->getId()]);
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