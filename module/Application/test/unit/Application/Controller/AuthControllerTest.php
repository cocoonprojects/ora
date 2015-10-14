<?php

namespace Application\Controller;


use Application\Authentication\AdapterResolver;
use Application\Authentication\OAuth2\InvalidTokenException;
use Application\Entity\User;
use Namshi\JOSE\SimpleJWS;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Authentication\Result;
use ZFX\Authentication\JWTBuilder;
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
	/**
	 * @var string
	 */
	private $privateKey = "-----BEGIN RSA PRIVATE KEY-----
MIIEpQIBAAKCAQEA8SJjXkniIeE6mEOvOuB940kni2v0E+UwqNrrmdJ49quZP48d
55k7t+OI9OFgQYLV7DW6u0tMGrvnuC+MD7nrFrwbSk74mO95C0C7TuU0k5S3OXFh
e72z34aibVXX+3oR0m1FU6qAuKqXkP8+Z5zJvSKy1i+EUD1zhkjdFhJ4z6ZsoHEp
VrnkI0QrUnWkKancw+e5BcR4uFbi3hgXdkILHsf4L4YeW9Tds4MOUEymm/hAcc4J
Xn95cDbOO51/Z+C6YPyjkWdzUHQ7TDaaboQTWY2YYeEi31dEvdcFM+ASmDkvcnft
AbZVmDi8oJzksztA1nmUoD8XQzTXxBOTSFGSnwIDAQABAoIBAQCykCeLhCTbt+Df
LogNjn5KmDqbaSbGsNrWv77mGtEOwUXrpjyb64Ioi2s5A8h22r81exg7Z+gEiA9w
+my6nI+1NzjyTwaSogs9xQKvytWjT+ZauFZa/sC7jwSq+H3HML8P13EWItXNai5w
5kK9EYLm91H9gBR4IvlwgHaPyMAD66bDyqKUL0C3oIbg9eMv4tgBkVzvkDnTXfMJ
RNNZ8iH2gjfzFPSeCy0dO79Q5B1Gu7drLCJRmhgqBFSsLgdOosqWnlfNWdExoCpr
FnapVwhi5kG38sfT1fpY6+2zPa3dVcYPPE7OuReZM+JhZDvvggo4JYDIjwF26E3L
YsmyJ4nZAoGBAP5TO1q0TD/o1p5AbJVygnX2U48aOYebldCfQEKY2z9us+dzTeo9
rYFTyppQ8+ctvmsCiHCM86jOHp5rdlowF9lWhaEjItoEpOPBTDMAhFc0RTvZW7Ka
Vj+0dsePKrVMUJ1wCplHUYYY4GPBcKn1jHQ0Hb/gQgRcLSYWPiz0khgTAoGBAPK4
6vlvxgeSklCjQbyBj1nTDV/1FZZbw9aofbiWPqtb/ofORWHPRda9nwOnIYSSupL1
v89yls3SLxDkzP5RE+NZYlsRXJzoO3UrvWTZ697t4Ex28T/v2zaC3M5fQY6XU6Z8
pXlFHEXJi7Is+xH41M6YdCo2/5xpO8vt0OZUgETFAoGBANjehrWRG5g/54to6m8C
B4eptpVHypj9rmII+pYPnJ5ZuyV5qI4/bA3lMtYmg+W1lzPPJCO9viVLJsb2YiUD
78JQSoEe4iBBZ44jjePL5A4sr4Eal1wUyclnDQac6dFRs0idexw7uaP84JOQJ492
qP+KVXgCNqlbJNDelMRnBZFrAoGANt1xz9xiKQgKpsugalnm62j3lv8xWAF6LSV8
9aKQm+95g30u1cMiiD3omczHmM4J+nouV1gRmoiuNuVhKQNuuW9U+jzccGNWPVAb
yZYw6P1gPCiOs+Ml7BZ8jvGdQfwW3oVCaj0i/Otn9miQgCl9AQ4ZBAnWkaZ/68Lf
+5CSRfkCgYEAngpwml1MunLUO1gFYk5PS0Elq6bjR7bEe8JegvqfqeM8IILpSyXo
NIWpPWGtI3X48gQiw0BdbrQkDJI76Qa/xcn0yIt+Z1dw5Uhxf2PsKJEhBaLvToTz
oGYZDHe7A05BzL5PD8vI3SeazAlpLidU6L40eZUeYj3+S7cthNr9MVU=
-----END RSA PRIVATE KEY-----";
	/**
	 * @var string
	 */
	private $publicKey = "-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA8SJjXkniIeE6mEOvOuB9
40kni2v0E+UwqNrrmdJ49quZP48d55k7t+OI9OFgQYLV7DW6u0tMGrvnuC+MD7nr
FrwbSk74mO95C0C7TuU0k5S3OXFhe72z34aibVXX+3oR0m1FU6qAuKqXkP8+Z5zJ
vSKy1i+EUD1zhkjdFhJ4z6ZsoHEpVrnkI0QrUnWkKancw+e5BcR4uFbi3hgXdkIL
Hsf4L4YeW9Tds4MOUEymm/hAcc4JXn95cDbOO51/Z+C6YPyjkWdzUHQ7TDaaboQT
WY2YYeEi31dEvdcFM+ASmDkvcnftAbZVmDi8oJzksztA1nmUoD8XQzTXxBOTSFGS
nwIDAQAB
-----END PUBLIC KEY-----";

	protected function setupController()
	{
		$authService = $this->getMockBuilder(AuthenticationServiceInterface::class)->getMock();
		$this->user = User::create();
		$result = new Result(Result::SUCCESS, $this->user);
		$authService->method('authenticate')->willReturn($result);
		$adapterResolver = $this->getMockBuilder(AdapterResolver::class)->getMock();
		return new AuthController($authService, $adapterResolver, $this->privateKey);
	}

	protected function setupRouteMatch()
	{
		return ['controller' => 'auth', 'action' => 'login'];
	}

	public function testLogin()
	{
		$this->controller->getAdapterResolver()
			->method('getAdapter')
			->willReturn('pippo');

		$this->request->setMethod('post');

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$arrayResult = json_decode($result->serialize(), true);
		$this->assertNotEmpty($arrayResult['token']);

		$jws = SimpleJWS::load($arrayResult['token']);
		$this->assertTrue($jws->isValid($this->publicKey, $this->controller->getAlgorithm()));
		$payload = $jws->getPayload();
		$this->assertEquals($this->user->getId(), $payload['uid']);
	}

	public function testLoginWithInvalidProvider()
	{
		$this->controller->getAdapterResolver()
			->method('getAdapter')
			->willReturn(null);
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$arrayResult = json_decode($result->serialize(), true);
		$this->assertEquals('Auth.InvalidProvider', $arrayResult['error']);
	}

	public function testLoginWithInvalidToken()
	{
		$this->controller->getAdapterResolver()
			->method('getAdapter')
			->will($this->throwException(new InvalidTokenException('Lorem Ipsum')));
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$arrayResult = json_decode($result->serialize(), true);
		$this->assertEquals('Lorem Ipsum', $arrayResult['error']);
	}
}