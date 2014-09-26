<?php

namespace Auth\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;

use Zend\View\Model\JsonModel;

class LogoutController extends AbstractRestfulController
{        
    public function getList()
    {
        $auth = new \Zend\Authentication\AuthenticationService();
        if($auth->hasIdentity())
        {
            $identity = $auth->getIdentity();
           
            $provider = $identity["provider"];

            if("" !== $provider)
            {
            	$me = $this->getServiceLocator()->get("ZendOAuth2\\".$provider);
         
        	    $auth->clearIdentity();
            	$session = $me->getSessionContainer();
            	$session->getManager()->getStorage()->clear();
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
