<?php
namespace Accounting\Controller;

use Accounting\Account;
use Accounting\OrganizationAccount;
use Accounting\Service\AccountService;
use Application\Entity\User;
use People\Organization;
use ZFX\Test\Controller\ControllerTest;

/**
 * Class DepositsControllerTest
 * @package Accounting\Controller
 * @group accounting
 */
class WithdrawalControllerTest extends ControllerTest
{
	/**
	 * @var Account
	 */
	protected $account;
	/**
	 * @var User
	 */
	protected $user;
	/**
	 * @var User
	 */
	protected $creator;
	
	protected function setupController()
	{
		$this->user = User::create();
		$this->creator = User::create();
		$organization = Organization::create('Lorem Ipsum', $this->creator);
		$this->account = OrganizationAccount::create($organization, $this->creator);
		$accountService = $this->getMockBuilder(AccountService::class)->getMock();
		$accountService
			->expects($this->any())
			->method('getAccount')
			->with($this->account->getId())
			->willReturn($this->account);

		return new WithdrawalsController($accountService);
	}
	
	protected function setupRouteMatch()
	{
		return [
			'controller' => 'withdrawals',
			'id'         => $this->account->getId()
		];
	}

	public function testInvoke() {
		$this->account->addHolder($this->user, $this->creator);
		$this->setupLoggedUser($this->user);

		$this->request->setMethod('post');
		$params = $this->request->getPost();
		$params->set('amount', 100);
		$params->set('description', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus iaculis.');
		 
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(201, $response->getStatusCode());
		$this->assertNotEmpty($response->getHeaders()->get('Location'));
	}

	public function testInvokeWithFloatAmount() {
		$this->account->addHolder($this->user, $this->creator);
		$this->setupLoggedUser($this->user);

		$this->request->setMethod('post');
		$params = $this->request->getPost();
		$params->set('amount', 100.56);
		$params->set('description', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus iaculis.');
		 
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(201, $response->getStatusCode());
		$this->assertNotEmpty($response->getHeaders()->get('Location'));
	}

	public function testInvokeAsNotHolder() {
		$this->setupLoggedUser($this->user);

		$this->request->setMethod('post');
		$params = $this->request->getPost();
		$params->set('amount', 100);
		$params->set('description', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus iaculis.');

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(403, $response->getStatusCode());
	}

	public function testInvokeWith0Amount() {
		$this->setupLoggedUser($this->user);

		$this->request->setMethod('post');
		$params = $this->request->getPost();
		$params->set('amount', 0);
		$params->set('description', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus iaculis.');
		 
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(400, $response->getStatusCode());
	}

	public function testInvokeWithNoAmount()
	{
		$this->setupLoggedUser($this->user);

		$this->request->setMethod('post');
		$params = $this->request->getPost();
		$params->set('description', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus iaculis.');
		 
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(400, $response->getStatusCode());
	}

	public function testInvokeWithNegativeAmount()
	{
		$this->setupLoggedUser($this->user);

		$this->request->setMethod('post');
		$params = $this->request->getPost();
		$params->set('amount', -1000);
		$params->set('description', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus iaculis.');
		 
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(400, $response->getStatusCode());
	}

	public function testInvokeAsAnonymous()
	{
		$this->setupAnonymous();

		$this->request->setMethod('post');
		$params = $this->request->getPost();
		$params->set('amount', 100);
		$params->set('description', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus iaculis.');

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(401, $response->getStatusCode());
	}
}