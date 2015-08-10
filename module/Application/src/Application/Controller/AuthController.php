<?php

namespace Application\Controller;

use Application\Authentication\AdapterResolver;
use Application\Authentication\OAuth2\InvalidTokenException;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;
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
	 * @var JWTBuilder
	 */
	private $builder;

	public function __construct(AuthenticationServiceInterface $authService, AdapterResolver $adapterResolver, JWTBuilder $builder) {
		$this->authService = $authService;
		$this->adapterResolver = $adapterResolver;
		$this->builder = $builder;
	}

	public function loginAction()
	{
		$json = new JsonModel();
		try {
			$adapter = $this->adapterResolver->getAdapter($this);
			if(is_null($adapter)) {
				$json->setVariable('error', 'Auth.InvalidProvider');
			} else {
				$result = $this->authService->authenticate($adapter);
				if($result->isValid()) {
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
}