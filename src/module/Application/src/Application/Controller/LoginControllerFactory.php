<?php

namespace Application\Controller;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class LoginControllerFactory implements FactoryInterface 
{
   /**
    * Default method to be used in a Factory Class
    * 
    * @see \Zend\ServiceManager\FactoryInterface::createService()
    */
    public function createService(ServiceLocatorInterface $serviceLocator) 
    {
      // dependency is fetched from Service Manager
      $loginService = $serviceLocator->getServiceLocator()->get('Application\Service\LoginService');
     
      // Controller is constructed, dependencies are injected (IoC in action)
      $controller = new \Application\Controller\LoginController($loginService); 
       
      return $controller; 
    }
}