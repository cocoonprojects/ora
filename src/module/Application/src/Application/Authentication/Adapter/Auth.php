<?php

namespace Application\Authentication\Adapter;

use ZendOAuth2\Authentication\Adapter\ZendOAuth2;
use Zend\EventManager\EventManager;
use Zend\Authentication\Result;

class Auth extends ZendOAuth2
{
	private $user;
	
	public function authenticate()
	{
		if(is_object($this->client)) {
		
			$args['code'] = Result::SUCCESS;
			$args['provider'] = $this->client->getProvider();
			
			$args['info']['provider'] = $args['provider'];
			$args['info']['user'] = $this->user;
			
			$args['token'] = (array)$this->client->getSessionToken();
		
			$args = $this->getEventManager()->prepareArgs($args);
		
			$this->getEventManager()->trigger('oauth2.success', $this, $args);
		
			return new Result($args['code'], $args['info']);
		
		} else {
		
			return new Result(Result::FAILURE, $this->client->getError());
		
		}		
	}
	
	public function setUserIdentity($user)
	{
		$this->user = $user;	
	}
	
	public function getInfoOfProvider($oauth2)
	{
		$this->setOAuth2Client($oauth2);
		
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
}