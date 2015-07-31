<?php

namespace Application\Authentication;


use Application\Entity\User;
use Namshi\JOSE\SimpleJWS;

class JWTUtils
{

	const PRIVATE_KEY = 'file:///var/www/ora/tests/ora.pem';

	const PUBLIC_KEY = 'file:///var/www/ora/tests/ora.pub';

	const TIME_TO_LIVE = 'P30D';

	const ALGORITHM = 'RS256';

	public static function buildJWT(User $identity)
	{
		$jwt = new SimpleJWS([
			'alg' => self::ALGORITHM
		]);

		$expireAt = new \DateTime();
		$expireAt->add(new \DateInterval(self::TIME_TO_LIVE));

		$jwt->setPayload([
			'uid' => $identity->getId(),
			'exp' => $expireAt->format('U')
		]);
		$privateKeyPath = self::getPrivateKeyPath();
		if(!($privateKey = openssl_pkey_get_private($privateKeyPath))) {
			throw new \Exception('Error loading private key ' . $privateKeyPath . ':' . openssl_error_string());
		}
		$jwt->sign($privateKey);
		return $jwt->getTokenString();
	}

	public static function getPayload($token)
	{
		$jws = SimpleJWS::load($token);
		$public_key = openssl_pkey_get_public(JWTUtils::getPublicKeyPath());
		if($jws->isValid($public_key, self::ALGORITHM)) {
			return $jws->getPayload();
		}
	}

	public static function getPrivateKeyPath()
	{
		$path = getenv('PRIVATE_KEY_PATH');
		return empty($path) ? self::PRIVATE_KEY : $path;
	}

	public static function getPublicKeyPath()
	{
		$path = getenv('PUBLIC_KEY_PATH');
		return empty($path) ? self::PUBLIC_KEY : $path;
	}
}