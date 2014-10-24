<?php

namespace ZendOAuth2\Authentication\Adapter;

use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use Zend\Authentication\Result;
use Zend\Authentication\Adapter\AdapterInterface;
use ZendOAuth2\AbstractOAuth2Client;

class ZendOAuth2 implements AdapterInterface, EventManagerAwareInterface
{
    
    protected $client;
    protected $events;   
    
    public function setOAuth2Client($oauth2)
    {
        if($oauth2 instanceof AbstractOAuth2Client) {
            $this->client = $oauth2;
        }       
    }
    
    public function authenticate()
    {
        if(is_object($this->client) AND is_object($this->client->getInfo())) { 
        
            $args['code'] = Result::SUCCESS;
            //$args['info'] = (array)$this->client->getInfo();
            $args['info'] = $this->getInfoOfProvider();
            $args['provider'] = $this->client->getProvider();
            $args['token'] = (array)$this->client->getSessionToken();
            
            $args = $this->getEventManager()->prepareArgs($args);
            
            $this->getEventManager()->trigger('oauth2.success', $this, $args);
                        
            return new Result($args['code'], $args['info']);
            
        } else {
            
            return new Result(Result::FAILURE, $this->client->getError());
        }        
    }

    public function getInfoOfProvider()
    {    	
    	$infoOfSession = array();
    	$info = (array)$this->client->getInfo();
    	
    	$infoOfSession['provider'] = $this->client->getProvider();
    	$infoOfSession['sessionOfProvider'] = $this->client->getSessionContainer()->getManager()->getStorage();
    	
    	switch($this->client->getProvider())
    	{
    		case 'google':
			    		$infoOfSession['firstname'] = $info['given_name'];
			    		$infoOfSession['lastname'] = $info['family_name'];
			    		$infoOfSession['picture'] = $info['picture'];
			    		$infoOfSession['email'] = $info['email'];    			
    					break;
    		case 'linkedin':
    					$infoOfSession['firstname'] = $info['firstName'];
    					$infoOfSession['lastname'] = $info['lastName'];
    					$infoOfSession['picture'] = $info['pictureUrl'];
    					$infoOfSession['email'] = $info['emailAddress'];    			
    					break; 
    		case 'TestProvider':
    					$infoOfSession['firstname'] = $info['name']; 
			    		$infoOfSession['lastname'] = $info['name'];
			    		$infoOfSession['picture'] = $info['picture'];
			    		$infoOfSession['email'] = $info['email']; 
    					break;    					   						
    	}
    	    	
    	return $infoOfSession;
    }
 
    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers(__CLASS__);
        $this->events = $events;
        return $this;
    }
    
    public function getEventManager()
    {
        if (null === $this->events) {
            $this->setEventManager(new EventManager());
        }
        return $this->events;
    }    
}