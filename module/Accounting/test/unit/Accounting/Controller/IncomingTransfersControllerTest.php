<?php

namespace Accounting\Controller;

use Accounting\Account;
use Accounting\Entity\PersonalAccount;
use Accounting\OrganizationAccount;
use Accounting\Service\AccountService;
use Application\Entity\User;
use Application\Service\UserService;
use People\Entity\Organization as ReadModelOrganization;
use People\Organization;
use People\Service\OrganizationService;
use ZFX\Test\Controller\ControllerTest;

/**
 * Class TransfersControllerTest
 * @package Accounting\Controller
 * @group accounting
 */
class IncomingTransfersControllerTest extends ControllerTest
{
	/**
	 * @var Organization
	 */
	private $organization;
	/**
	 * @var OrganizationAccount
	 */
	private $account;
	/**
	 * @var User
	 */
	private $payer;
	/**
	 * @var User
	 */
	private $user;
	/**
	 * @var Account
	 */
	private $payerAccount;

	protected function setupController()
	{
		$this->user = User::create();

		$creator = User::create();
		$organization = Organization::create('Lorem Ipsum', $creator);
		$this->organization = new ReadModelOrganization($organization->getId());
		$organizationService = $this->getMockBuilder(OrganizationService::class)->getMock();
		$organizationService
			->method('findOrganization')
			->willReturn($this->organization);

		$this->account = OrganizationAccount::create($organization, $creator);

		$this->payer = User::create();
		$this->payer->setEmail('john.doe@foo.com');
		$userServiceStub = $this->getMockBuilder(UserService::class)->getMock();
		$userServiceStub
			->expects($this->any())
			->method('findUserByEmail')
			->with($this->payer->getEmail())
			->willReturn($this->payer);

		$this->payerAccount = Account::create($organization, $this->payer);
		$accountService = $this->getMockBuilder(AccountService::class)->getMock();
		return new IncomingTransfersController($accountService, $userServiceStub, $organizationService);
	}

	/**
	 * @return array
	 */
	protected function setupRouteMatch()
	{
		return [
			'controller' => 'incoming-transfers',
			'orgId' => $this->organization->getId(),
			'id' => $this->account->getId()
		];
	}

	public function testInvokeAsAnonymous()
	{
		$this->setupAnonymous();

		$this->request->setMethod('post');
		$params = $this->request->getPost();
		$params->set('amount', 100);
		$params->set('description', 'Morbi sit amet nulla dolor');
		$params->set('payer', $this->payer->getEmail());

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(401, $response->getStatusCode());
	}

	public function testInvokeWithNoAmount()
	{
		$this->account->addHolder($this->user, $this->user);
		$this->setupLoggedUser($this->user);

		$this->request->setMethod('post');
		$params = $this->request->getPost();
		$params->set('description', 'Morbi sit amet nulla dolor');
		$params->set('payer', $this->payer->getEmail());

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(400, $response->getStatusCode());
	}

	public function testInvokeWithNotANumberAmount()
	{
		$this->account->addHolder($this->user, $this->user);
		$this->setupLoggedUser($this->user);

		$this->request->setMethod('post');
		$params = $this->request->getPost();
		$params->set('amount', 'a');
		$params->set('description', 'Morbi sit amet nulla dolor');
		$params->set('payer', $this->payer->getEmail());

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(400, $response->getStatusCode());
	}

	public function testInvokeWith0Amount()
	{
		$this->account->addHolder($this->user, $this->user);
		$this->setupLoggedUser($this->user);

		$this->request->setMethod('post');
		$params = $this->request->getPost();
		$params->set('amount', 0);
		$params->set('description', 'Morbi sit amet nulla dolor');
		$params->set('payer', $this->payer->getEmail());

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(400, $response->getStatusCode());
	}

	public function testInvokeWithNegativeAmount()
	{
		$this->account->addHolder($this->user, $this->user);
		$this->setupLoggedUser($this->user);

		$this->request->setMethod('post');
		$params = $this->request->getPost();
		$params->set('amount', -100);
		$params->set('description', 'Morbi sit amet nulla dolor');
		$params->set('payer', $this->payer->getEmail());

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(400, $response->getStatusCode());
	}

	public function testInvokeWithoutPayee()
	{
		$this->account->addHolder($this->user, $this->user);
		$this->setupLoggedUser($this->user);

		$this->request->setMethod('post');
		$params = $this->request->getPost();
		$params->set('amount', 100);
		$params->set('description', 'Morbi sit amet nulla dolor');

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(400, $response->getStatusCode());
	}

	public function testInvokeAsAccountNotHolder()
	{
		$this->user->addMembership($this->organization);
		$this->setupLoggedUser($this->user);

		$this->controller->getAccountService()
			->expects($this->once())
			->method('getAccount')
			->with($this->account->getId())
			->willReturn($this->account);

		$this->request->setMethod('post');
		$params = $this->request->getPost();
		$params->set('amount', 100);
		$params->set('description', 'Morbi sit amet nulla dolor');
		$params->set('payer', $this->payer->getEmail());

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(403, $response->getStatusCode());
	}

	public function testInvokeWithPayeeOfOtherOrganization()
	{
		$this->account->addHolder($this->user, $this->user);
		$this->setupLoggedUser($this->user);

		$this->controller->getAccountService()
			->expects($this->once())
			->method('getAccount')
			->with($this->account->getId())
			->willReturn($this->account);

		$this->controller->getAccountService()
			->expects($this->once())
			->method('findPersonalAccount')
			->with($this->payer, $this->organization)
			->willReturn(null);

		$this->request->setMethod('post');
		$params = $this->request->getPost();
		$params->set('amount', 100);
		$params->set('description', 'Morbi sit amet nulla dolor');
		$params->set('payer', $this->payer->getEmail());

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(400, $response->getStatusCode());
	}

	public function testInvoke()
	{
		$this->account->addHolder($this->user, $this->user);
		$this->setupLoggedUser($this->user);

		$this->controller->getAccountService()
			->expects($this->once())
			->method('findPersonalAccount')
			->with($this->payer, $this->organization)
			->willReturn(new PersonalAccount($this->payerAccount->getId(), $this->organization));

		$this->controller->getAccountService()
			->method('getAccount')
			->will($this->onConsecutiveCalls($this->account, $this->payerAccount));

		$this->request->setMethod('post');
		$params = $this->request->getPost();
		$params->set('amount', 100);
		$params->set('description', 'Morbi sit amet nulla dolor');
		$params->set('payer', $this->payer->getEmail());

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(201, $response->getStatusCode());
	}
}