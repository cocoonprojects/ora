<?php
namespace Accounting\Controller;

use ZendExtension\Mvc\Controller\AbstractHATEOASRestfulController;
use Ora\Accounting\AccountService;
use Ora\Accounting\IllegalAmountException;
use Zend\Validator\ValidatorChain;
use Zend\I18n\Validator\Float;
use Zend\Validator\NotEmpty;
use Zend\Validator\GreaterThan;

class DepositsController extends AbstractHATEOASRestfulController
{
	protected static $collectionOptions = [];
	protected static $resourceOptions = ['POST'];
	/**
	 *
	 * @var AccountService
	 */
	protected $accountService;
	
	public function __construct(AccountService $accountService) {
		$this->accountService = $accountService;
	}
	
	public function invoke($id, $data) {
		$amount = $data['amount'];
		$amountValidator = new ValidatorChain();
		$amountValidator->attach(new NotEmpty())
						->attach(new Float())
						->attach(new GreaterThan(['min' => 0, 'inclusive' => true]));
		if(!$amountValidator->isValid($amount)) {
			$this->response->setStatusCode(400);
			return $this->response;
		}
		
		$payer = $this->identity()['user'];
		if(is_null($payer)) {
			$this->response->setStatusCode(401);
			return $this->response;
		}
		
		$description = isset($data['description']) ? $data['description'] : null;
		
        $account = $this->accountService->getAccount($id);
        if(is_null($account)) {
        	$this->response->setStatusCode(404);
            return $this->response;            	
        }
        
		$this->transaction()->begin();
		try {
			$account->deposit($amount, $payer, $description);
			$this->transaction()->commit();
			$this->response->setStatusCode(201); // Created
			$this->response->getHeaders()->addHeaderLine(
					'Location',
					$this->url()->fromRoute('accounts', array('id' => $account->getId()))
			);
		} catch (IllegalAmountException $e) {
			$this->transaction()->rollback();
			$this->response->setStatusCode(400);
		} catch (IllegalPayerException $e) {
			$this->transaction()->rollback();
			$this->response->setStatusCode(400);
		}
		
		return $this->response;
	}

	protected function getCollectionOptions() {
		return self::$collectionOptions;
	}
	
	protected function getResourceOptions() {
		return self::$resourceOptions;
	}
	
}