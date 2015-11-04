<?php
namespace Accounting\View;

use Application\Entity\User;
use People\Entity\Organization;
use Zend\Permissions\Acl\Acl;
use Zend\View\Model\JsonModel;
use Zend\Json\Json;
use Accounting\Entity\Account;
use Accounting\Entity\OrganizationAccount;
use Zend\Mvc\Controller\Plugin\Url;

class AccountsJsonModel extends JsonModel
{
	/**
	 * @var Url
	 */
	protected $url;
	/**
	 * @var User
	 */
	protected $identity;
	/**
	 * @var Acl
	 */
	protected $acl;
	/**
	 * @var Organization
	 */
	private $organization;

	public function __construct(Url $url, User $identity, Acl $acl, Organization $organization)
	{
		$this->url = $url;
		$this->identity = $identity;
		$this->acl = $acl;
		$this->organization = $organization;
	}

	public function serialize()
	{
		$resource = $this->getVariable('resource');
		if(is_array($resource)) {
			$hal['_links']['self']['href'] = $this->url->fromRoute('accounts', ['orgId' => $this->organization->getId()]);
			$hal['_embedded']['ora:account'] = array_map(array($this, 'serializeOne'), $resource);
			$hal['count'] = count($resource);
			$hal['total'] = count($resource);
		} else {
			$hal = $this->serializeOne($resource);
		}
		return Json::encode($hal);
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

	protected function serializeBalance($account) {
		return array(
			'value' => $account->getBalance()->getValue(),
			'date' => date_format($account->getBalance()->getDate(), 'c'),
		);
	}

	protected function serializeLinks($account) {
		$rv['self']['href'] = $this->url->fromRoute('accounts', ['orgId' => $account->getOrganization()->getId(), 'id' => $account->getId()]);
		if($this->acl->isAllowed($this->identity, $account, 'Accounting.Account.statement')) {
			$rv['ora:statement']['href'] = $this->url->fromRoute('statements', ['orgId' => $account->getOrganization()->getId(), 'controller' => 'personal-statement']);
		}
		if($this->acl->isAllowed($this->identity, $account, 'Accounting.Account.deposit')) {
			$rv['ora:deposit']['href'] = $this->url->fromRoute('accounts', ['orgId' => $account->getOrganization()->getId(), 'id' => $account->getId(), 'controller' => 'deposits']);
		}
		if($this->acl->isAllowed($this->identity, $account, 'Accounting.Account.withdrawal')) {
			$rv['ora:withdrawal']['href'] = $this->url->fromRoute('accounts', ['orgId' => $account->getOrganization()->getId(), 'id' => $account->getId(), 'controller' => 'withdrawals']);
		}
		if($this->acl->isAllowed($this->identity, $account, 'Accounting.Account.incoming-transfer')) {
			$rv['ora:incoming-transfer']['href'] = $this->url->fromRoute('accounts', ['orgId' => $account->getOrganization()->getId(), 'id' => $account->getId(), 'controller' => 'incoming-transfer']);
		}
		if($this->acl->isAllowed($this->identity, $account, 'Accounting.Account.outgoing-transfer')) {
			$rv['ora:outgoing-transfer']['href'] = $this->url->fromRoute('accounts', ['orgId' => $account->getOrganization()->getId(), 'id' => $account->getId(), 'controller' => 'outgoing-transfer']);
		}
		return $rv;
	}
}
