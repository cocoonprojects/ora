<?php
namespace Accounting\Controller;

use ZendExtension\Mvc\Controller\AbstractHATEOASRestfulController;
use Ora\Accounting\AccountService;
use Ora\Accounting\Account;

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
	 * @var Account
	 */
	private $account;
	
	public function __construct(AccountService $accountService) {
		$this->accountService = $accountService;
	}

	public function preDispatch($e)
	{
		$id = $this->params()->fromRoute('id');
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
		if(!$this->authService->hasIdentity()) {
			$this->response->setStatusCode(401);
			return $this->response;
		}
		
		$amount = $data['amount'];
		$payer = $this->authService->getIdentity()['user'];		
		$this->account->deposit($amount, $payer);
		
	}
}