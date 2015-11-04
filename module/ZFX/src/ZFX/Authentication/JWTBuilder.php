<?php

namespace ZFX\Authentication;


use Application\Entity\User;
use Namshi\JOSE\SimpleJWS;

class JWTBuilder
{
	/**
	 * @var string
	 */
	private $timeToLive = 'P30D';
	/**
	 * @var string
	 */
	private $algorithm = 'RS256';
	/**
	 * @var string
	 */
	private $privateKey;

	/**
	 * JWTBuilder constructor.
	 *
	 * @param string $privateKey
	 */
	public function __construct($privateKey)
	{
		$this->privateKey = $privateKey;
	}
	public function buildJWT(User $identity)
	{
		$jwt = new SimpleJWS([
			'alg' => $this->algorithm
		]);

		$expireAt = new \DateTime();
		$expireAt->add(new \DateInterval($this->timeToLive));

		$jwt->setPayload([
			'uid' => $identity->getId(),
			'exp' => $expireAt->format('U')
		]);
		$jwt->sign($this->privateKey);
		return $jwt->getTokenString();
	}

	/**
	 * @param string $timeToLive
	 * @return JWTBuilder
	 */
	public function setTimeToLive($timeToLive)
	{
		$this->timeToLive = $timeToLive;
		return $this;
	}

	/**
	 * @param string $algorithm
	 * @return JWTBuilder
	 */
	public function setAlgorithm($algorithm)
	{
		$this->algorithm = $algorithm;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getTimeToLive()
	{
		return $this->timeToLive;
	}

	/**
	 * @return string
	 */
	public function getAlgorithm()
	{
		return $this->algorithm;
	}

	/**
	 * @return string
	 */
	public function getPrivateKey()
	{
		return $this->privateKey;
	}

}