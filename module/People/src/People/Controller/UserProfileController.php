<?php

namespace People\Controller;

use Application\Entity\User;
use People\Service\OrganizationService;
use Application\Controller\OrganizationAwareController;
use Application\Service\UserService;
use People\View\UserProfileJsonModel;
use Accounting\Service\AccountService;
use Accounting\Entity\PersonalAccount;

class UserProfileController extends OrganizationAwareController
{
	protected static $collectionOptions = array('GET');
	protected static $resourceOptions = array('DELETE', 'POST', 'GET', 'PUT');
	
	private $orgService;
	private $userService;
	private $accountService;
	
	public function __construct(OrganizationService $orgService, UserService $userService, AccountService $accountService) {
		$this->orgService = $orgService;
		$this->userService = $userService;
		$this->accountService = $accountService;
	}
	
	public function get($id)
	{		
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}
		
		$user = $this->userService->findUser($id);
		if(is_null($user)){
			$this->response->setStatusCode(404);
			return $this->response;
		}
		
		$membership = $user->getMembershipOf($this->organization->getId());
		if(is_null($membership)){
			$this->response->setStatusCode(404);
			return $this->response;
		}
		$role = $membership->getRole();
		
		$account = $this->accountService->findPersonalAccount($user, $this->organization);
		$actualBalance = $account->getBalance()->getValue();
		
		$transactions = $account->getTransactions();
		$totalGeneratedCredits = 0;
		
		//Date Limits
		$dateLimitThreeMonths = new \DateTime();
		$dateLimitThreeMonths->modify('-3 month');//3 Months
		
		$dateLimitSixMonths = new \DateTime();
		$dateLimitSixMonths->modify('-6 month');//6 Months
		
		$dateLimitOneYear = new \DateTime();
		$dateLimitOneYear->modify('-1 year');//One Year
		
		$lastThreeMonthsCredits = 0;
		$lastSixMonthsCredits = 0;
		$lastYearCredits = 0;
		
		foreach ($transactions as $t){
			if($t->getCreatedAt()<$dateLimitOneYear)
				break;
			
			if($t->getAmount()>= 0){
				$totalGeneratedCredits+=$t->getAmount();
				if($t->getCreatedAt()>$dateLimitThreeMonths){
					$lastThreeMonthsCredits+=$t->getAmount();//Last 3 Months
				}
				if($t->getCreatedAt()>$dateLimitSixMonths){
					$lastSixMonthsCredits+=$t->getAmount();//Last 6 Months
				}
				if ($t->getCreatedAt()>$dateLimitOneYear){
					$lastYearCredits+=$t->getAmount();//Last year
				}
			}
		}
		
		$view = new UserProfileJsonModel();
		$view->setVariable('org-resource', $this->organization);
		$view->setVariable('user-resource', $user);
		$view->setVariable('role-resource', $role);
		$view->setVariable('account-balance', $actualBalance);
		$view->setVariable('total-gen-credits', $totalGeneratedCredits);
		$view->setVariable('last-3-month', $lastThreeMonthsCredits);
		$view->setVariable('last-6-month', $lastSixMonthsCredits);
		$view->setVariable('last-year', $lastYearCredits);
		
		return $view;
	}
	
	public function getList()
	{
		// HTTP STATUS CODE 405: Method not allowed
		$this->response->setStatusCode(405);
			
		return $this->response;
	}
	
	public function create($data)
	{
		// HTTP STATUS CODE 405: Method not allowed
		$this->response->setStatusCode(405);
			
		return $this->response;
	}
	
	public function update($id, $data)
	{
		// HTTP STATUS CODE 405: Method not allowed
		$this->response->setStatusCode(405);
			
		return $this->response;
	}
	
	public function replaceList($data)
	{
		// HTTP STATUS CODE 405: Method not allowed
		$this->response->setStatusCode(405);
			
		return $this->response;
	}
	
	public function deleteList()
	{
		// HTTP STATUS CODE 405: Method not allowed
		$this->response->setStatusCode(405);
			
		return $this->response;
	}
	
	public function delete($id)
	{
		// HTTP STATUS CODE 405: Method not allowed
		$this->response->setStatusCode(405);
			
		return $this->response;
	}	
	
	public function getOrganizationService()
	{
		return $this->orgService;
	}
	
	public function getUserService()
	{
		return $this->userService;
	}
	
	public function getAccountService()
	{
		return $this->accountService;
	}
	
	protected function getCollectionOptions()
	{
		return self::$collectionOptions;
	}
	
	protected function getResourceOptions()
	{
		return self::$resourceOptions;
	}
	
}