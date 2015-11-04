<?php

namespace Application\Controller;

use Application\Authentication\AdapterResolver;
use Application\Authentication\OAuth2\InvalidTokenException;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use ZFX\Authentication\JWTBuilder;

/**
 * Class AuthController
 * @package Application\Controller
 * @deprecated
 */
class AuthController extends AbstractActionController
{
	/**
	 *
	 * @var AdapterResolver
	 */
	private $adapterResolver;
	/**
	 *
	 * @var AuthenticationServiceInterface
	 */
	private $authService;
	/**
	 * @var string
	 */
	private $algorithm = 'RS256';
	/**
	 * @var string
	 */
	private $timeToLive = 'P30D';
	/**
	 * @var JWTBuilder
	 */
	private $builder;

	/**
	 * @param AuthenticationServiceInterface $authService
	 * @param AdapterResolver $adapterResolver
	 * @param string $privateKey
	 */
	public function __construct(AuthenticationServiceInterface $authService, AdapterResolver $adapterResolver, $privateKey)
	{
		$this->authService = $authService;
		$this->adapterResolver = $adapterResolver;
		$this->builder = new JWTBuilder($privateKey);
	}

	public function loginAction()
	{
		$json = new JsonModel();
		try {
			$adapter = $this->adapterResolver->getAdapter($this);
			if (is_null($adapter)) {
				$json->setVariable('error', 'Auth.InvalidProvider');
			} else {
				$result = $this->authService->authenticate($adapter);
				if ($result->isValid()) {
					$json->setVariable('token', $this->builder->buildJWT($result->getIdentity()));
				} else {
					$json->setVariable('error', $result->getMessages());
				}
			}
		} catch (InvalidTokenException $e) {
			return $json->setVariable('error', $e->getMessage());
		}
		return $json;
	}

	public function getAdapterResolver()
	{
		return $this->adapterResolver;
	}

	/**
	 * @return string
	 */
	public function getAlgorithm()
	{
		return $this->builder->getAlgorithm();
	}

	/**
	 * @param string $algorithm
	 * @return AuthController
	 */
	public function setAlgorithm($algorithm)
	{
		$this->builder->setAlgorithm($algorithm);
		return $this;
	}

	/**
	 * @return string
	 */
	public function getTimeToLive()
	{
		return $this->builder->getTimeToLive();
	}

	/**
	 * @param string $timeToLive
	 * @return AuthController
	 */
	public function setTimeToLive($timeToLive)
	{
		$this->builder->setTimeToLive($timeToLive);
		return $this;
	}

}