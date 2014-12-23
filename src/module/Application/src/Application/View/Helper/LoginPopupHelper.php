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
		$authService = $this->getServiceLocator()->get('Application\Service\AuthenticationService');
		if(!$authService->hasIdentity())
		{
			$output = "<div id='popupLogin' class='modal fade'>
					<div class='modal-dialog'>
						<div class='modal-content'>
							<div class='modal-header'>
								<button type='button' class='close' data-dismiss='modal'>
									<span aria-hidden='true'>&times;</span>
									<span class='sr-only'>Close</span>
								</button>
								<h4 class='modal-title'>Effettua il login</h4>
							</div>
							<div class='modal-body'>
								<center>";

			$adapterResolver = $this->getServiceLocator()->get('Application\Service\AdapterResolver');
			foreach($adapterResolver->getProviders() as $provider => $instance)
			{
				$output .=  "<a onclick=\"auth.openAuthWindow('{$instance->getUrl()}'); return false;\" class='btn btn-success btn-lg' href='#'>Login con {$provider}</a>&nbsp;";
			}
			
			$output .=" </center>
		              </div>
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