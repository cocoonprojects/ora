<?php

namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class LoginHelper extends AbstractHelper implements ServiceLocatorAwareInterface {
	/**
	 *
	 * @var ServiceLocatorInterface
	 */
	private $serviceLocator;
	public function __invoke() {
		$authService = $this->getServiceLocator ()->get ( 'Zend\Authentication\AuthenticationService' );
		if (! $authService->hasIdentity ()) {
			$output = '';
			
			$adapterResolver = $this->getServiceLocator ()->get ( 'Application\Service\AdapterResolver' );
			$signin = array ();
			foreach ( $adapterResolver->getProviders () as $provider => $instance ) {
				switch ($provider) {
					case 'linkedin' :
						$btn = ' btn-linkedin" style="background-color: #007BB6; color: white; text-transform: none';
						$icon = ' fa-linkedin';
						break;
					default :
						$btn = '';
						$icon = '';
				}
				$url = $instance->getUrl ();
				$signin [] = '<a class="btn btn-block btn-social' . $btn . '" href="' . $url . '"><i class="fa' . $icon . '"></i>Sign in with ' . ucfirst ( $provider ) . '</a>';
			}
			$output .= empty ( $signin ) ? '' : implode ( ' ', $signin );
			
			return $output;
		}
	}
	public function setServiceLocator(ServiceLocatorInterface $helperPluginManager) {
		$this->serviceLocator = $helperPluginManager->getServiceLocator ();
		return $this;
	}
	public function getServiceLocator() {
		return $this->serviceLocator;
	}
}