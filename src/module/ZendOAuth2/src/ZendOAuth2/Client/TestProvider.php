<?php

namespace ZendOAuth2\Client;

use ZendOAuth2\AbstractOAuth2Client;
use ZendOAuth2\ClientOptions;
use Zend\Http\PhpEnvironment\Request;

class TestProvider extends AbstractOAuth2Client
{

	protected $providerName = 'TestProvider';

	public function getUrl()
	{		
		$url = $this->options->getAuthUri();
		$this->generateState();
		return $url;

	}


	public function getToken(Request $request)
	{
		if($request->getQuery('code'))
		{
			$this->getInfo();
			return true;
		}
		else 
			return false;
	}
	
	protected function generateState()
	{
		$this->session->state = "12345678901";
		return $this->session->state;
	}
	
	public function getInfo()
	{
		if (is_object($this->session->info)) {
			return $this->session->info;
		}
	
		try {
				$this->session->info = \Zend\Json\Decoder::decode("{}");
				/*
				 * { "emailAddress": "...",
				*   "firstName": "...",
				*   "lastName": "...",
				*   "pictureUrl": "https://media.licdn.com/..."
				*   }
				* */
				$this->session->info->name = "Utente Test";
				$this->session->info->picture = "";
				$this->session->info->email = "test@test.it";
	
			return $this->session->info;
	
		} catch (\Zend\Json\Exception\RuntimeException $e) {
				
			$this->error['internal-error'] = 'Unknown error. '.$e->getMessage();
			$this->error['sessionInfo'] = array();
	
			return false;
				
		}
	}	
		
	
	

}
