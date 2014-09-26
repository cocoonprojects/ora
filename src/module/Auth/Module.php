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

        $auth = new \Zend\Authentication\AuthenticationService();

        if($auth->hasIdentity())
        {
            $viewModel->logged = true;
            $viewModel->user = $auth->getIdentity();
        }
        else
        {
            $sm = $e->getApplication()->getServiceManager();
            $config = $sm->get('Config');

            $avaiablesProvider = $config["zendoauth2"];

            $urlList = array();

            foreach($avaiablesProvider as $provider => $providerValue)
            {
                $provider = ucfirst($provider);
                $serviceProvider = "ZendOAuth2\\".$provider;

                $me = $sm->get($serviceProvider);

                $urlList[$provider] =  $me->getUrl();
            }

            $viewModel->urlAuthList = $urlList;
            $viewModel->logged = false;
        }
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
