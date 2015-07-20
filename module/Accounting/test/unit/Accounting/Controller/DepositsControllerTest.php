<?php
namespace Accounting\Controller;

use People\Organization;
use ZFX\Test\Controller\ControllerTest;
use Application\Entity\User;
use Application\Controller\Plugin\EventStoreTransactionPlugin;
use Accounting\Account;
use Accounting\Service\AccountService;

class DepositsControllerTest extends ControllerTest
{
	protected $account;
	
	protected function setupController()
	{
		$accountService = $this->getMockBuilder(AccountService::class)->getMock();
		return new DepositsController($accountService);
	}
	
	protected function setupRouteMatch()
	{
		return array('controller' => 'deposits');
	}

	protected function setUp()
	{
		parent::setUp();
		$user = User::create();
		$this->setupLoggedUser($user);

		$organization = Organization::create('Lorem Ipsum', $user);
		
		$this->account = Account::create($organization, $user);
	}
	
	public function testInvoke() {
		$this->controller->getAccountService()
			->expects($this->once())
			->method('getAccount')
			->with($this->account->getId())
			->willReturn($this->account);
		
		$this->controller->getAccountService()
			->method('deposit')
			->willReturn($this->account);
		
		$this->routeMatch->setParam('id', $this->account->getId());

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
		$this->controller->getAccountService()
			->expects($this->once())
			->method('getAccount')
			->with($this->account->getId())
			->willReturn($this->account);
		
		$this->controller->getAccountService()
			->method('deposit')
			->willReturn($this->account);
		
		$this->routeMatch->setParam('id', $this->account->getId());

		$this->request->setMethod('post');
		$params = $this->request->getPost();
		$params->set('amount', 100.56);
		$params->set('description', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus iaculis.');
		 
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(201, $response->getStatusCode());
		$this->assertNotEmpty($response->getHeaders()->get('Location'));
	}

	public function testInvokeWith0Amount() {
	   	$this->routeMatch->setParam('id', $this->account->getId());

		$this->request->setMethod('post');
		$params = $this->request->getPost();
		$params->set('amount', 0);
		$params->set('description', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus iaculis.');
		 
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(400, $response->getStatusCode());
	}

	public function testInvokeWithNoAmount() {
		$this->routeMatch->setParam('id', $this->account->getId());

		$this->request->setMethod('post');
		$params = $this->request->getPost();
		$params->set('description', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus iaculis.');
		 
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(400, $response->getStatusCode());
	}

	public function testInvokeWithNegativeAmount() {
		$this->routeMatch->setParam('id', $this->account->getId());

		$this->request->setMethod('post');
		$params = $this->request->getPost();
		$params->set('amount', -1000);
		$params->set('description', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus iaculis.');
		 
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(400, $response->getStatusCode());
	}

	public function testInvokeAsAnonymous() {
		$this->setupAnonymous();

		$this->routeMatch->setParam('id', $this->account->getId());
		
		$this->request->setMethod('post');
		
		$params = $this->request->getPost();
		$params->set('amount', 100);
		$params->set('description', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus iaculis.');
				
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(401, $response->getStatusCode());
	}
}