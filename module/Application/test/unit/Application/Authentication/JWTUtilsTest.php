<?php

namespace Application\Authentication;


use Application\Entity\User;
use Namshi\JOSE\SimpleJWS;

/**
 * Class JWTUtilsTest
 * @package Application\Authentication
 * @group auth
 */
class JWTUtilsTest extends \PHPUnit_Framework_TestCase
{
	public function testBuildJWT()
	{
		$identity = User::create();
		$token = JWTUtils::buildJWT($identity);

		$jws = SimpleJWS::load($token);
		$public_key = openssl_pkey_get_public(JWTUtils::getPublicKeyPath());
		$this->assertTrue($jws->isValid($public_key, 'RS256'));
		$payload = $jws->getPayload();
		$this->assertEquals($identity->getId(), $payload['uid']);
		$this->assertNotEmpty($payload['exp']);
	}

}