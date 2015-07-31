<?php

namespace Application\Controller;


use Application\Authentication\AdapterResolver;
use Application\Authentication\JWTUtils;
use Application\Entity\User;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Authentication\Result;
use ZFX\Test\Controller\ControllerTest;

/**
 * Class AuthControllerTest
 * @package Application\Controller
 * @group auth
 */
class AuthControllerTest extends ControllerTest
{
	/**
	 * @var User
	 */
	private $user;

	protected function setupController()
	{
		$authService = $this->getMockBuilder(AuthenticationServiceInterface::class)->getMock();
		$this->user = User::create();
		$result = new Result(Result::SUCCESS, $this->user);
		$authService->method('authenticate')->willReturn($result);
		$adapterResolver = $this->getMockBuilder(AdapterResolver::class)->getMock();
		$adapterResolver->method('getAdapter')->willReturn('pippo');
		return new AuthController($authService, $adapterResolver);
	}

	protected function setupRouteMatch()
	{
		return ['controller' => 'auth', 'action' => 'login'];
	}

	public function testLogin()
	{
		$this->request->setMethod('post');

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$arrayResult = json_decode($result->serialize(), true);
		$this->assertNotEmpty($arrayResult['token']);
		$payload = JWTUtils::getPayload($arrayResult['token']);
		$this->assertEquals($this->user->getId(), $payload['uid']);
	}

}