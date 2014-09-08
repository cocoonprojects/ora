<?php
namespace Accounting\View;

use Zend\View\Model\JsonModel;
use Zend\Json\Json;
use Ora\CreditsAccount\CreditsAccount;

class CreditsAccountJsonModel extends JsonModel
{
	public function serialize()
	{
		$resource = $this->getVariable('resource');
		$url = $this->getVariable('url');
		if(is_array($resource)) {
			$representation = array();
			foreach ($resource as $account) {
				$representation[] = $this->serializeOne($account, $url);
			}
		} else {
			$representation = $this->serializeOne($resource, $url);
		}
		return Json::encode($representation);
	}
	
	private function serializeOne(CreditsAccount $account, $url) {
		$rv = array(
			'id' => $account->getId(),
			'createdAt' => $account->getCreatedAt(),
			'balance' => array('value' => $account->getBalance()->getValue(),
								'date' => $account->getBalance()->getDate(),
			),
		);
		if(!is_null($url)) {
			$rv['_links'] = array('self' => $url.'/'.$account->getId()); 
		}
		return $rv;
	}
}