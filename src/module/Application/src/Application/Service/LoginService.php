<?php

namespace Application\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;

/**
 * Service Login
 *
 * @author CarmatiCRM <dev@carmati.it>
 *
 */
class LoginService implements EventManagerAwareInterface 
{

  /**
   * Event Manager
   * 
   * @var \Zend\EventManager\EventManagerInterface
   */
  private $eventManager;

  /*
   * Constructs service 
   * 
   */
  public function __construct() 
  {

  }

  public function login()
  {
    return "";
  }

  /**
   * Injects Event Manager (ZF2 component) into this class
   *
   * @see \Zend\EventManager\EventManagerAwareInterface::setEventManager()
   */
  public function setEventManager(EventManagerInterface $events)
  {
       $events->setIdentifiers(array(
           __CLASS__,
           get_called_class(),
       ));
       $this->eventManager = $events;
       return $this;
   }

   /**
    * Fetches Event Manager (ZF2 component) from this class
    *
    * @see \Zend\EventManager\EventsCapableInterface::getEventManager()
    */
   public function getEventManager()
   {
       if (null === $this->eventManager) {
           $this->setEventManager(new EventManager());
       }
       return $this->eventManager;
   }

}