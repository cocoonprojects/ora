<?php
namespace Auth;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

     public function onBootstrap(\Zend\Mvc\MvcEvent $e)
     {
        $em = $e->getApplication()->getEventManager();
        
        $em->attach(\Zend\Mvc\MvcEvent::EVENT_DISPATCH, array($this, 'onDispatch'));
    }

    public function getServiceConfig() 
    {
        return array(
                'invokables' => array(
                    'Auth\Service\AuthService' => 'Auth\Service\AuthService'
            )
        );
    }
    
    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $application = $e->getParam('application');
        $viewModel = $application->getMvcEvent()->getViewModel();

        $viewVariables['logged'] = false;
        $viewVariables['urlAuthList'] = array();
        $viewVariables['user'] = "";
        
        $sm = $e->getApplication()->getServiceManager();
        $authService = $sm->get('\Auth\Service\AuthService');
        
        $viewVariables = $authService->informationsOfAuthentication();
        
		$viewModel->logged = $viewVariables['logged'];
        $viewModel->user = $viewVariables['user'];
		$viewModel->urlAuthList = $viewVariables['urlAuthList'];


    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getControllerConfig() {
        
        return array(
            'invokables' => array(
                'Auth\Controller\Login' => 'Auth\Controller\LoginController',
                'Auth\Controller\Logout' => 'Auth\Controller\LogoutController',
            ),
        );
        
    } 
}
