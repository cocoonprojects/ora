<?php
/**
 * Created by PhpStorm.
 * User: andreabandera
 * Date: 03/11/15
 * Time: 23:27
 */

namespace Accounting\Controller;


use Accounting\Service\AccountService;
use Application\Controller\OrganizationAwareController;
use People\Service\OrganizationService;

class MembersController extends OrganizationAwareController
{
	protected static $resourceOptions = ['GET'];
	/**
	 * @var AccountService
	 */
	private $accountService;

	/**
	 * MembersController constructor.
	 * @param OrganizationService $organizationService
	 * @param AccountService $accountService
	 */
	public function __construct(OrganizationService $organizationService, AccountService $accountService)
	{
		parent::__construct($organizationService);
		$this->accountService = $accountService;
	}

	/**
	 * Return single resource
	 *
	 * @param  string $id
	 * @return mixed
	 */
	public function get($id)
	{
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}

		$account = $this->accountService->findPersonalAccount($id, $this->organization);
		if(is_null($account)){
			$this->response->setStatusCode(404);
			return $this->response;
		}

		$url = $this->url()->fromRoute('accounts', ['orgId' => $this->organization->getId(), 'id' => $account->getId()]);
		$this->response->getHeaders()->addHeaderLine('Location', $url);
		$this->response->setStatusCode(301);
		return $this->response;
	}

	/**
	 * @return array
	 */
	protected function getResourceOptions()
	{
		return self::$resourceOptions;
	}
}