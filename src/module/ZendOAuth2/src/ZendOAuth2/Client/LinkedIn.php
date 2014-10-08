<?php

namespace ZendOAuth2\Client;

use ZendOAuth2\AbstractOAuth2Client;
use Zend\Http\PhpEnvironment\Request;

class LinkedIn extends AbstractOAuth2Client
{
    
    protected $providerName = 'linkedin';

    public function getUrl()
    {
        $url = $this->options->getAuthUri().'?'
            . 'redirect_uri='  . urlencode($this->options->getRedirectUri())
            . '&response_type=code'
            . '&client_id='    . $this->options->getClientId()
            . '&state='        . $this->generateState()
            . $this->getScope(' ');

        return $url;
        
    }
    
    public function getToken(Request $request)
    {
        if(isset($this->session->token)) {
            return true;
        } elseif (
            strlen($this->session->state) > 0 AND
            $this->session->state == $request->getQuery('state') AND
            strlen($request->getQuery('code')) > 5
        ) {
            $client = $this->getHttpClient();
            $client->setUri($this->options->getTokenUri());
            $client->setMethod(Request::METHOD_POST);
            $client->setParameterPost(array(
                'code'          => $request->getQuery('code'),
                'client_id'     => $this->options->getClientId(),
                'client_secret' => $this->options->getClientSecret(),
                'redirect_uri'  => $this->options->getRedirectUri(),
                'grant_type'    => 'authorization_code'
            ));
            $retVal = $client->send()->getBody();

            try {
                $token = \Zend\Json\Decoder::decode($retVal);
                if(isset($token->access_token) AND $token->expires_in > 0) {
                    $this->session->token = $token;
                    return true;
                } else {
                    $this->error  = array(
                        'internal-error' => 'LinkedIn settings error.',
                        'error'          => $token->error,
                        'token'          => $token
                    );
                    return false;
                }
            } catch (\Zend\Json\Exception\RuntimeException $e) {
                $this->error['internal-error'] = 'Unknown error.';
                $this->error['token'] = $retVal;
                return false;
            }
        } else {
        	
            $this->error = array(
                'internal-error'=> 'State error, request variables do not match the session variables.',
                'session-state' => $this->session->state, 
                'request-state' => $request->getQuery('state'), 
                'code'          => $request->getQuery('code')
            );
            return false;
        }
    }

    public function getHttpclientResponse()
    {
    	$urlProfile = $this->options->getInfoUri();
    	
    	$headers = array(
    			'Authorization: Bearer ' . $this->session->token->access_token,
    			'x-li-format : json', // Comment out to use XML
    	);
    	
    	$request = curl_init();
    	curl_setopt($request, CURLOPT_URL, $urlProfile);
    	curl_setopt($request, CURLOPT_HTTPHEADER, $headers);
    	curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
    	
    	$retVal = curl_exec($request);
    	
    	if(curl_getinfo($request, CURLINFO_HTTP_CODE) != '200')
    	{
    		$retVal = null;
    	}
    	curl_close($request); 

    	return $retVal;
    }
    
    public function getInfo()
    {
        if (is_object($this->session->info)) {
            return $this->session->info;
        }

        if (isset($this->session->token->access_token)) {
		
	        try {
		        	$retVal = $this->getHttpclientResponse();
		        
		        	//var_dump($retVal);
		            if (strlen(trim($retVal)) > 0) {
		            	
		            	$this->session->info = \Zend\Json\Decoder::decode($retVal);
		            	
		                /*
		                 * { "emailAddress": "...", 
		                 *   "firstName": "...", 
		                 *   "lastName": "...", 
		                 *   "pictureUrl": "https://media.licdn.com/..." 
		                 *   }
		                 * */
		                $this->session->info->name = $this->session->info->firstName." ".$this->session->info->lastName;
		                $this->session->info->picture = $this->session->info->pictureUrl;
		                $this->session->info->email = $this->session->info->emailAddress;
		                
		                return $this->session->info;
		            } else {
		                $this->error = array('internal-error' => 'Get info return value is empty.');
		               
		                return false;
	            	}
	            } catch (\Zend\Json\Exception\RuntimeException $e) {
	            
	            	$this->error['internal-error'] = 'Unknown error. '.$e->getMessage();
	            	$this->error['sessionInfo'] = $retVal;
	            	
	            	return false;
	            
	            }            
        } else {

            $this->error = array('internal-error' => 'Session access token not found.');
            return false;
        }
    }


}
