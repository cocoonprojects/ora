<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class LoginPopupHelper extends AbstractHelper implements ServiceLocatorAwareInterface
{
	/**
	 * 
	 * @var ServiceLocatorInterface
	 */
	private $serviceLocator;
	
	public function __invoke()
	{
		$authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
		if(!$authService->hasIdentity())
		{
			$output = '<div id="loginModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal">
									<span aria-hidden="true">&times;</span>
									<span class="sr-only">Close</span>
								</button>
								<h4 class="modal-title">Sign in with</h4>
							</div>
							<div class="modal-body" style="text-align: center">';

			$adapterResolver = $this->getServiceLocator()->get('Application\Service\AdapterResolver');
			$signin = array();
			foreach($adapterResolver->getProviders() as $provider => $instance)
			{
				$url = $instance->getUrl();
				$signin[] = '<a class="btn btn-primary btn-lg" href="' . $url . '">' . $provider . '</a>';
			}
			$output .= empty($signin) ? '' : implode(' ', $signin);
			$output .="</div>
		            </div><!-- /.modal-content -->
		          </div><!-- /.modal-dialog -->
		        </div><!-- /.modal -->";			
			
			return $output;		
		}		
	}
	
	public function setServiceLocator(ServiceLocatorInterface $helperPluginManager)
	{
		$this->serviceLocator = $helperPluginManager->getServiceLocator();
		return $this;
	}

	public function getServiceLocator() {
		return $this->serviceLocator;
	}
	
}