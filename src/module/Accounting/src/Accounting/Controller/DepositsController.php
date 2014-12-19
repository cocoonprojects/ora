<?php
namespace Accounting\Controller;

use ZendExtension\Mvc\Controller\AbstractHATEOASRestfulController;
use Ora\Accounting\AccountService;
use Ora\Accounting\Account;
use Zend\Authentication\AuthenticationServiceInterface;
use Ora\Accounting\IllegalAmountException;

class DepositsController extends AbstractHATEOASRestfulController
{
	protected static $collectionOptions = ['POST'];
	protected static $resourceOptions = [];
	/**
	 *
	 * @var AccountService
	 */
	protected $accountService;
	/**
	 * 
	 * @var AuthenticationServiceInterface
	 */
	protected $authService;
	/**
	 * 
	 */
	protected $transactionManager;
	/**
	 * 
	 * @var Account
	 */
	private $account;
	
	public function __construct(AccountService $accountService, AuthenticationServiceInterface $authService) {
		$this->accountService = $accountService;
		$this->authService = $authService;
	}
	
	public function setTransactionManager($transactionManager) {
		$this->transactionManager = $transactionManager;
	}

	public function preDispatch($e)
	{
		$id = $this->params()->fromRoute('accountId');
        if (!empty($id)) 
        {
            // Check if task with specified ID exist
        	$this->account = $this->accountService->getAccount($id);
            if(is_null($this->account)) {
                $this->response->setStatusCode(404);
                return $this->response;            	
            }
        }
	}
	
	public function create($data) {
		$response = $this->getResponse();
		
		if(!$this->authService->hasIdentity()) {
			$response->setStatusCode(401);
			return $response;
		}
		
		$amount = $data['amount'];
		$description = isset($data['description']) ? $data['description'] : null;
		$payer = $this->authService->getIdentity()['user'];
		
		$this->transactionManager->beginTransaction();
		try {
			$this->account->deposit($amount, $payer, $description);
		} catch (IllegalAmountException $e) {
			$response->setStatusCode(400);
			return $response;
		} catch (IllegalPayerException $e) {
			$response->setStatusCode(400);
			return $response;
		} finally {
			$this->transactionManager->commit();
		}
		
		$response->setStatusCode(201); // Created
		$response->getHeaders()->addHeaderLine(
				'Location',
				$this->url()->fromRoute('accounts', array('id' => $this->account->getId()))
		);
		return $response;
	}

	protected function getCollectionOptions() {
		return self::$collectionOptions;
	}
	
	protected function getResourceOptions() {
		return self::$resourceOptions;
	}
	
}