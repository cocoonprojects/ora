<?php
namespace Accounting\View;

use Zend\View\Model\JsonModel;
use Zend\Json\Json;
use Ora\ReadModel\Account;
use Ora\ReadModel\OrganizationAccount;
use Zend\Mvc\Controller\Plugin\Url;

class AccountsJsonModel extends JsonModel
{
	private $url;
	
	public function __construct(Url $url) {
		$this->url = $url;	
	} 
	
	public function serialize()
	{
		$resource = $this->getVariable('resource');
		if(is_array($resource)) {
			$representation['accounts'] = [];
			foreach ($resource as $account) {
				$representation['accounts'][] = $this->serializeOne($account);
			}
		} else {
			$representation = $this->serializeOne($resource);
		}
		return Json::encode($representation);
	}
	
	private function serializeOne(Account $account) {
		$rv = array(
			'id' => $account->getId(),
			'createdAt' => date_format($account->getCreatedAt(), 'c'),
			'balance' => array('value' => $account->getBalance()->getValue(),
								'date' => date_format($account->getBalance()->getDate(), 'c'),
			),
		);
		$rv['_links']['self'] = $this->url->fromRoute('accounts', ['id' => $account->getId()]); 
		$rv['_links']['statement'] = $this->url->fromRoute('accounts', ['id' => $account->getId(), 'controller' => 'statements']);
		if($account instanceof OrganizationAccount) {
			$rv['_links']['deposits'] = $this->url->fromRoute('accounts', ['accountId' => $account->getId(), 'controller' => 'deposits']);
		}
		return $rv;
	}
}