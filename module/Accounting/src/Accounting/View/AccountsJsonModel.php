<?php
namespace Accounting\View;

use Zend\View\Model\JsonModel;
use Zend\Json\Json;
use Accounting\Entity\Account;
use Accounting\Entity\OrganizationAccount;
use Zend\Mvc\Controller\Plugin\Url;

class AccountsJsonModel extends StatementJsonModel
{
	public function serialize()
	{
		$resource = $this->getVariable('resource');
		if(is_array($resource)) {
			$representation['accounts'] = array_map(array($this, 'serializeOne'), $resource);
		} else {
			$representation = $this->serializeOne($resource);
		}
		return Json::encode($representation);
	}
	
	protected function serializeOne(Account $account) {
		$rv['balance'] = $this->serializeBalance($account);
		$rv['createdAt'] = date_format($account->getCreatedAt(), 'c');
		if($account instanceof OrganizationAccount) {
			$rv['organization'] = $account->getOrganization()->getName();
		}
		$rv['_links'] = $this->serializeLinks($account);
		return $rv;
	}
	
	protected function serializeLinks($account) {
		$rv['self'] = $this->url->fromRoute('accounts', ['orgId' => $account->getOrganization()->getId(), 'id' => $account->getId()]);
		if($this->acl->isAllowed($this->user, $account, 'Accounting.Account.statement')){		 
			$rv['statement'] = $this->url->fromRoute('accounts', ['orgId' => $account->getOrganization()->getId(), 'id' => $account->getId(), 'controller' => 'statement']);
		}
		if($this->acl->isAllowed($this->user, $account, 'Accounting.Account.deposit')){
			$rv['deposits'] = $this->url->fromRoute('accounts', ['orgId' => $account->getOrganization()->getId(), 'id' => $account->getId(), 'controller' => 'deposits']);
		}
		return $rv;
	}
	
}