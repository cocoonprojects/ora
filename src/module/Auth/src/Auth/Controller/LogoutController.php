<?php

namespace Auth\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\Authentication\AuthenticationService;
use Zend\View\Model\JsonModel;

class LogoutController extends AbstractRestfulController
{        
    public function getList()
    {
        $response = array();

        $me = $this->getServiceLocator()->get('ZendOAuth2\Google');
        $session = $me->getSessionContainer();
        $session->getManager()->getStorage()->clear();

        
        return new JsonModel(array('data' => $response));
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
