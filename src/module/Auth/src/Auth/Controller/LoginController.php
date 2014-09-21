<?php

namespace Auth\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Session\Container;

class LoginController extends AbstractRestfulController
{    
    /***
     * Redirect Uri
     */
    public function get($id)
    {
        $token = "";
        $info = "";
        $url = "";
        $provider = "";
        
        $config = $this->getServiceLocator()->get('Config');

        $avaiablesProvider = $config["zendoauth2"];

        if ("" != $id && array_key_exists($id, $avaiablesProvider))
        {
            $provider = ucfirst($id);
            $serviceProvider = "ZendOAuth2\\".$provider;

            $me = $this->getServiceLocator()->get($serviceProvider);

            if (strlen($this->params()->fromQuery('code')) > 10) {

                if($me->getToken($this->request)) 
                {
                    $token = $me->getSessionToken();

                    $auth = new \Zend\Authentication\AuthenticationService();

                    $adapter = $this->getServiceLocator()->get('ZendOAuth2\Auth\Adapter'); 
                    $adapter->setOAuth2Client($me); 
                    $rs = $auth->authenticate($adapter);

                    if (!$rs->isValid()) {
                        foreach ($rs->getMessages() as $message) {
                            echo "$message\n";
                        }
                    } else {
                        $container = new Container("Zend_Auth");
                        $identityArray = $auth->getIdentity();
                        $identityArray["provider"] = $provider;

                        $auth->getStorage()->write($identityArray);
                    }

                } else {
                	
                    $token = $me->getError();
                    print_r($token); die();
                }

                $info = $me->getInfo();

            } else {

                $url = $me->getUrl();

            }
        }

        return $this->redirect()->toRoute('home');
    }

    public function getResponseWithHeader()
    {
        $response = $this->getResponse();
        $response->getHeaders()
                 ->addHeaderLine('Access-Control-Allow-Origin','*')
                 ->addHeaderLine('Access-Control-Allow-Methods','GET');
        
        return $response;
    }
}
