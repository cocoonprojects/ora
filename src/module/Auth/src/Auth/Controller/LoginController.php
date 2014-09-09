<?php

namespace Auth\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

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
                } else {
                    $token = $me->getError();
                }

                $info = $me->getInfo();

            } else {

                $url = $me->getUrl();

            }
        }

        return new JsonModel(array(
            'token' => $token,
            'info' => $info, 
            'url' => $url
            )
        );
        
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
