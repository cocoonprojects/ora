<?php
namespace Accounting\Controller;

use Accounting\Entity\Account;
use Accounting\Entity\OrganizationAccount;
use Accounting\Service\AccountService;
use Application\Controller\OrganizationAwareController;
use Application\Service\UserService;
use People\Service\OrganizationService;
use Zend\View\Model\JsonModel;

class AccountsController extends OrganizationAwareController
{
	protected static $collectionOptions = ['GET'];
	protected static $resourceOptions = ['GET'];
	/**
	 * @var AccountService
	 */
	protected $accountService;
	/**
	 * @var UserService
	 */
	private $userService;

	/**
	 * @param OrganizationService $organizationService
	 * @param AccountService $accountService
	 * @param UserService $userService
	 */
	public function __construct(OrganizationService $organizationService, AccountService $accountService, UserService $userService) {
		parent::__construct($organizationService);
		$this->accountService = $accountService;
		$this->userService = $userService;
	}

	/**
	 * @return \Zend\Stdlib\ResponseInterface|JsonModel
	 */
	public function getList()
	{
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}
		if(!$this->isAllowed($this->identity(), $this->organization, 'Accounting.Accounts.list')){
			$this->response->setStatusCode(403);
			return $this->response;
		}

		$email = $this->params()->fromQuery('email');
		if(!empty($email)) {
			$user = $this->userService->findUserByEmail($email);
			$account = $this->accountService->findPersonalAccount($user, $this->organization);
			return new JsonModel([
				'count' => 1,
				'total' => 1,
				'_embedded' => [
					'ora:account' => [$this->serializeAccount($account)]
				],
				'_links' => [
					'self' => [
						'href' => $this->url()->fromRoute('accounts', ['orgId' => $this->organization->getId()])
					]
				]
			]);
		}

		$this->response->setStatusCode(400);
		return $this->response;
	}

	/**
	 * Return single resource
	 *
	 * @param  mixed $id
	 * @return mixed
	 */
	public function get($id)
	{
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}

		$account = $this->accountService->findAccount($id);
		if(is_null($account)){
			$this->response->setStatusCode(404);
			return $this->response;
		}

		if(!$this->isAllowed($this->identity(), $account, 'Accounting.Account.get')) {
			$this->response->setStatusCode(403);
			return $this->response;
		}

		$endOn = $this->getDateTimeParam('endOn');
		if(is_null($endOn)) {
			$endOn = new \DateTimeImmutable();
		}
		$transactions = $this->accountService->findTransactions($account, null, null, ['endOn' => $endOn]);
		$totalGeneratedCredits = 0;

		//Date Limits
		$dateLimitThreeMonths = $endOn->sub(new \DateInterval('P3M'));
		$dateLimitSixMonths = $endOn->sub(new \DateInterval('P6M'));
		$dateLimitOneYear = $endOn->sub(new \DateInterval('P1Y'));

		$lastThreeMonthsCredits = 0;
		$lastSixMonthsCredits = 0;
		$lastYearCredits = 0;

		foreach ($transactions as $t) {
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

		return new JsonModel([
			'balance' => $account->getBalance()->getValue(),
			'total'   => $totalGeneratedCredits,
			'last3M'  => $lastThreeMonthsCredits,
			'last6M'  => $lastSixMonthsCredits,
			'last1Y'  => $lastYearCredits
		]);
	}

	/**
	 * @return AccountService
	 */
	public function getAccountService()
	{
		return $this->accountService;
	}

	/**
	 * @return UserService
	 */
	public function getUserService()
	{
		return $this->userService;
	}

	/**
	 * @return array
	 */
	protected function getCollectionOptions()
	{
		return self::$collectionOptions;
	}

	/**
	 * @return array
	 */
	protected function getResourceOptions()
	{
		return self::$resourceOptions;
	}

	/**
	 * @param Account $account
	 * @return array
	 */
	private function serializeAccount(Account $account) {
		$rv = [
			'id' => $account->getId(),
			'type' => $account instanceof OrganizationAccount ? 'shared' : 'personal',
			'balance' => [
				'value' => $account->getBalance()->getValue(),
				'date' => date_format($account->getBalance()->getDate(), 'c'),
			],
			'createdAt' => date_format($account->getCreatedAt(), 'c'),
			'_links' => [
				'self' => [
					'href' => $this->url()->fromRoute('accounts', ['orgId' => $account->getOrganization()->getId(), 'id' => $account->getId()])
				]
			]
		];
		if($this->isAllowed($this->identity(), $account, 'Accounting.Account.statement')) {
			$rv->_links['ora:statement']['href'] = $this->url()->fromRoute('statements', ['orgId' => $account->getOrganization()->getId(), 'controller' => 'personal-statement']);
		}
		if($this->isAllowed($this->identity(), $account, 'Accounting.Account.deposit')) {
			$rv->_links['ora:deposit']['href'] = $this->url()->fromRoute('accounts', ['orgId' => $account->getOrganization()->getId(), 'id' => $account->getId(), 'controller' => 'deposits']);
		}
		if($this->isAllowed($this->identity(), $account, 'Accounting.Account.withdrawal')) {
			$rv->_links['ora:withdrawal']['href'] = $this->url()->fromRoute('accounts', ['orgId' => $account->getOrganization()->getId(), 'id' => $account->getId(), 'controller' => 'withdrawals']);
		}
		if($this->isAllowed($this->identity(), $account, 'Accounting.Account.incoming-transfer')) {
			$rv->_links['ora:incoming-transfer']['href'] = $this->url()->fromRoute('accounts', ['orgId' => $account->getOrganization()->getId(), 'id' => $account->getId(), 'controller' => 'incoming-transfer']);
		}
		if($this->isAllowed($this->identity(), $account, 'Accounting.Account.outgoing-transfer')) {
			$rv->_links['ora:outgoing-transfer']['href'] = $this->url()->fromRoute('accounts', ['orgId' => $account->getOrganization()->getId(), 'id' => $account->getId(), 'controller' => 'outgoing-transfer']);
		}
		if($account instanceof OrganizationAccount) {
			$rv['organization'] = $account->getOrganization()->getName();
		}
		return $rv;
	}
}
