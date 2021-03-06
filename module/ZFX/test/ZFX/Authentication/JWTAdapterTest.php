<?php

namespace ZFX\Authentication;


use Application\Authentication\OAuth2\LoadLocalProfileListener;
use Application\Entity\User;
use Application\Service\UserService;
use Namshi\JOSE\SimpleJWS;
use Zend\Authentication\Result;

/**
 * Class JWTAdapterTest
 * @package ZFX\Authentication
 * @group auth
 */
class JWTAdapterTest extends \PHPUnit_Framework_TestCase
{
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
	/**
	 * @var JWTAdapter
	 */
	private $adapter;
	/**
	 * @var LoadLocalProfileListener
	 */
	private $listener;
	/**
	 * @var JWTBuilder
	 */
	private $builder;

	protected function setUp()
	{
		$this->builder = new JWTBuilder(
			"-----BEGIN RSA PRIVATE KEY-----
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
-----END RSA PRIVATE KEY-----");
		$this->adapter = new JWTAdapter($this->publicKey);
		$googleClient = new \Google_Client();
		$userService = $this->getMockBuilder(UserService::class)->getMock();
		$this->listener = new LoadLocalProfileListener($userService, $googleClient);
		$this->listener->attach($this->adapter->getEventManager());
	}

	public function testAuthenticate()
	{
		$user = User::create();

		$this->listener->getUserService()
			->method('findUser')
			->willReturn($user);

		$token = $this->builder->buildJWT($user);

		$this->adapter->setToken($token);
		$result = $this->adapter->authenticate();

		$this->assertEquals(Result::SUCCESS, $result->getCode());
		$this->assertEquals($user, $result->getIdentity());
	}

	public function testAuthenticateWithExpiredToken()
	{
		$jwt = new SimpleJWS([
			'alg' => $this->builder->getAlgorithm()
		]);

		$expireAt = new \DateTime();
		$expireAt->sub(new \DateInterval('P1D'));

		$jwt->setPayload([
			'uid' => '1',
			'exp' => $expireAt->format('U')
		]);
		$jwt->sign($this->builder->getPrivateKey());
		$token = $jwt->getTokenString();

		$this->adapter->setToken($token);
		$result = $this->adapter->authenticate();

		$this->assertEquals(Result::FAILURE_CREDENTIAL_INVALID, $result->getCode());
		$this->assertNull($result->getIdentity());
	}

	public function testAuthenticateWithNotExistingIdentity()
	{
		$this->listener->getUserService()
			->method('findUser')
			->willReturn(null);

		$user = User::create();
		$token = $this->builder->buildJWT($user);

		$this->adapter->setToken($token);
		$result = $this->adapter->authenticate();

		$this->assertEquals(Result::FAILURE_IDENTITY_NOT_FOUND, $result->getCode());
		$this->assertNull($result->getIdentity());
	}

	public function testParseCorruptedToken()
	{
		$jwt = new SimpleJWS([
			'alg' => $this->builder->getAlgorithm()
		]);

		$expireAt = new \DateTime();
		$expireAt->add(new \DateInterval('P1D'));

		$jwt->setPayload([
			'uid' => '1',
			'exp' => $expireAt->format('U')
		]);
		$jwt->sign($this->builder->getPrivateKey());
		$token = $jwt->getTokenString();

		$jwt->setPayload([
			'uid' => '2',
		]);
		$editedToken = $jwt->getTokenString();

		$token = substr($editedToken, 0, strrpos($editedToken, '.')) . substr($token, strrpos($token, '.'));

		$this->adapter->setToken($token);
		$result = $this->adapter->authenticate();

		$this->assertEquals(Result::FAILURE_CREDENTIAL_INVALID, $result->getCode());
		$this->assertNull($result->getIdentity());
	}
}