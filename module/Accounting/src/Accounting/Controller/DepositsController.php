<?php
namespace Accounting\Controller;

use Zend\I18n\Validator\Float;
use Zend\Validator\ValidatorChain;
use Zend\Validator\NotEmpty;
use Zend\Validator\GreaterThan;
use ZFX\Rest\Controller\HATEOASRestfulController;
use Accounting\Service\AccountService;
use Accounting\IllegalAmountException;

class DepositsController extends HATEOASRestfulController
{
	protected static $collectionOptions = [];
	protected static $resourceOptions = ['POST'];
	/**
	 *
	 * @var AccountService
	 */
	private $accountService;
	/**
	 * @var ValidatorChain
	 */
	private $amountValidator;
	
	public function __construct(AccountService $accountService) {
		$this->accountService = $accountService;
		$this->amountValidator = new ValidatorChain();
		$this->amountValidator
			->attach(new NotEmpty())
			->attach(new Float())
			->attach(new GreaterThan(['min' => 0, 'inclusive' => false]));
	}
	
	public function invoke($id, $data) {
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}

		if(!isset($data['amount']) || !$this->amountValidator->isValid($data['amount'])) {
			$this->response->setStatusCode(400);
			return $this->response;
		}

		$description = isset($data['description']) ? trim($data['description']) : null;
		
		$account = $this->accountService->getAccount($id);
		if(is_null($account)) {
			$this->response->setStatusCode(404);
			return $this->response;
		}

		if(!$this->isAllowed($identity, $account, 'Accounting.Account.deposit')) {
			$this->response->setStatusCode(403);
			return $this->response;
		}

		$this->transaction()->begin();
		try {
			$account->deposit($data['amount'], $this->identity(), $description);
			$this->transaction()->commit();
			$this->response->setStatusCode(201); // Created
			$this->response->getHeaders()->addHeaderLine(
					'Location',
					$this->url()->fromRoute('accounts', array('orgId' => $account->getOrganizationId(),'id' => $account->getId()))
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
	
	public function getAccountService() {
		return $this->accountService;
	}

	protected function getCollectionOptions() {
		return self::$collectionOptions;
	}
	
	protected function getResourceOptions() {
		return self::$resourceOptions;
	}
	
}
