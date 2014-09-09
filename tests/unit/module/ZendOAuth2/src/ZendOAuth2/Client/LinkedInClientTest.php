<?php

namespace ZendOAuth2\Client;

use PHPUnit_Framework_TestCase;
use Zend\Mvc\Application;

class LinkedInClientTest extends PHPUnit_Framework_TestCase
{
    
    protected $providerName = 'LinkedIn';
    
    public function setup()
    {
         $this->client = $this->getClient();
         
         $this->httpClientMock = $this->getMockBuilder('\ZendOAuth2\Client\LinkedIn')
         								->setMethods(array('getHttpclientResponse'))
         								->getMock();         
    }
    
    public function tearDown()
    {

        unset($this->client->getSessionContainer()->token);
        unset($this->client->getSessionContainer()->state);
        unset($this->client->getSessionContainer()->info);
        
        $session = $this->httpClientMock->getSessionContainer();
        $session->getManager()->getStorage()->clear();
        
    }
    
    public function getClient()
    {
        $me = new \ZendOAuth2\Client\LinkedIn;
        
        $cf = array(
            'zendoauth2' => array(
                'linkedin' => array(
                    'scope' => array(
                        /*
                        'user',
                        'public_repo',
                        'repo',
                        'repo:status',
                        'delete_repo',
                        'gist'
                        */
                    ),
                    'auth_uri'      => 'https://www.linkedin.com/uas/oauth2/authorization',
                    'token_uri'     => 'https://www.linkedin.com/uas/oauth2/accessToken',
                    'info_uri'      => 'https://api.linkedin.com/v1/people/~',
                    'client_id'     => 'your api key',
                    'client_secret' => 'your api secret',
                    'redirect_uri'  => 'your callback url which links to your controller',
                ),
            )
        );
        
        $bootstrap = Application::init(include 'tests/unit/test.config.php');
        $me->setOptions(new \ZendOAuth2\ClientOptions($cf['zendoauth2']['linkedin']));
        return $me;
    }
    
    public function testInstanceTypes()
    {
        $this->assertInstanceOf('ZendOAuth2\AbstractOAuth2Client', $this->client);
        $this->assertInstanceOf('ZendOAuth2\Client\\'.$this->providerName, $this->client);
        $this->assertInstanceOf('ZendOAuth2\ClientOptions', $this->client->getOptions());
        $this->assertInstanceOf('Zend\Session\Container', $this->client->getSessionContainer());
        $this->assertInstanceOf('ZendOAuth2\OAuth2HttpClient', $this->client->getHttpClient());
    }
    
    public function testGetProviderName()
    {
        $this->assertSame(strtolower($this->providerName), $this->client->getProvider());
    }
    
    public function testSetHttpClient()
    {
        $httpClientMock = $this->getMock(
                '\ZendOAuth2\OAuth2HttpClient',
                null,
                array(null, array('timeout' => 30, 'adapter' => '\Zend\Http\Client\Adapter\Curl'))
        );
        
        $this->client->setHttpClient($httpClientMock);
    }
    
    public function testFailSetHttpClient()
    {
        $this->setExpectedException('ZendOAuth2\Exception\HttpClientException');
        $this->client->setHttpClient(new \Zend\Http\Client);
    }
    
    public function testSessionState()
    {        
        $this->assertEmpty($this->client->getState());
        $this->client->getUrl();
        $this->assertEquals(strlen($this->client->getState()), 32);        
    }
    
    public function testLoginUrlCreation()
    {        
        $uri = \Zend\Uri\UriFactory::factory($this->client->getUrl());
        $this->assertTrue($uri->isValid());        
    }
    
    public function testGetScope()
    {
        
        if(count($this->client->getOptions()->getScope()) > 0) {
            $this->assertTrue(strlen($this->client->getScope()) > 0);
        } else {
            $this->assertTrue(strlen($this->client->getScope()) == 0);
            $this->client->getOptions()->setScope(array('some', 'scope'));
            $this->assertTrue(strlen($this->client->getScope()) > 0);
        }
        
    }
    
    public function testFailGetToken()
    {
        
        $this->client->getUrl();
        
        $request = new \Zend\Http\PhpEnvironment\Request;
        
        $this->assertFalse($this->client->getToken($request));
        
        $request->setQuery(new \Zend\Stdlib\Parameters(array('code' => 'some code', 'state' => 'some state')));      
        
        $this->assertFalse($this->client->getToken($request));
        $error = $this->client->getError();
        $this->assertStringEndsWith('variables do not match the session variables.', $error['internal-error']);
        
        if(!getenv('ZF2_PATH')) {
            
            $request->setQuery(new \Zend\Stdlib\Parameters(array('code' => 'some code', 'state' => $this->client->getState())));
            $this->assertFalse($this->client->getToken($request));
            $error = $this->client->getError();
            $this->assertStringEndsWith('settings error.', $error['internal-error']);
            
        }
        
    }
    
    public function testFailGetTokenMocked()
    {
        
        $this->client->getUrl();
        
        $httpClientMock = $this->getMock(
            '\ZendOAuth2\OAuth2HttpClient',
            array('send'),
            array(null, array('timeout' => 30, 'adapter' => '\Zend\Http\Client\Adapter\Curl'))
        );
        
        $httpClientMock->expects($this->any())
                        ->method('send')
                        ->will($this->returnCallback(array($this, 'getFaultyMockedTokenResponse')));
        
        $this->client->setHttpClient($httpClientMock);
        
        $request = new \Zend\Http\PhpEnvironment\Request;
        
        $request->setQuery(new \Zend\Stdlib\Parameters(array('code' => 'some code', 'state' => $this->client->getState())));
        $this->assertFalse($this->client->getToken($request));      

        $error = $this->client->getError();
        $this->assertStringEndsWith('settings error.', $error['internal-error']);
        
    }
    
    public function testFailGetTokenMockedNonJson()
    {
    
        $this->client->getUrl();
    
        $httpClientMock = $this->getMock(
                '\ZendOAuth2\OAuth2HttpClient',
                array('send'),
                array(null, array('timeout' => 30, 'adapter' => '\Zend\Http\Client\Adapter\Curl'))
        );
    
        $httpClientMock->expects($this->any())
                        ->method('send')
                        ->will($this->returnCallback(array($this, 'getFaultyMockedNonJsonTokenResponse')));
    
        $this->client->setHttpClient($httpClientMock);
    
        $request = new \Zend\Http\PhpEnvironment\Request;
        $request->setQuery(new \Zend\Stdlib\Parameters(array('code' => 'some code', 'state' => $this->client->getState())));
    
        $this->assertFalse($this->client->getToken($request));
    
        $error = $this->client->getError();
        $this->assertStringEndsWith('Unknown error.', $error['internal-error']);
    
    }
    
    public function testGetTokenMocked()
    {
        
        $this->client->getUrl();
        
        $httpClientMock = $this->getMock(
            '\ZendOAuth2\OAuth2HttpClient',
            array('send'),
            array(null, array('timeout' => 30, 'adapter' => '\Zend\Http\Client\Adapter\Curl'))
        );
        
        $httpClientMock->expects($this->exactly(1))
                        ->method('send')
                        ->will($this->returnCallback(array($this, 'getMockedTokenResponse')));
        
        $this->client->setHttpClient($httpClientMock);
        
        $request = new \Zend\Http\PhpEnvironment\Request;
        $request->setQuery(new \Zend\Stdlib\Parameters(array('code' => 'some code', 'state' => $this->client->getState())));
        
        $this->assertTrue($this->client->getToken($request));
        $this->client->getToken($request);
        
        $this->assertTrue($this->client->getToken($request)); // from session
        
        $this->assertTrue(strlen($this->client->getSessionToken()->access_token) > 0);
        
    }
    
    public function testGetInfo()
    {       
    	$this->httpClientMock->method('getHttpclientResponse')
    					->will($this->returnCallback(array($this, 'getMockedInfoResponse')));
 
        $token = new \stdClass; // fake the session token exists
        $token->access_token = 'some';
        $this->httpClientMock->getSessionContainer()->token = $token;
        
        $info = $this->httpClientMock->getInfo();
        
        $this->assertSame('John Doe', $info->name);
        $this->assertSame('JohnDoe@email.it', $info->email);
        $this->assertSame('pictureUrl', $info->picture);

    }
    
    public function testFailNoReturnGetInfo()
    {    
    	$this->httpClientMock->method('getHttpclientResponse')
    							->will($this->returnCallback(array($this, 'getMockedInfoResponseEmpty')));
    	
    	$token = new \stdClass; // fake the session token exists
    	$token->access_token = 'some';
    	$this->httpClientMock->getSessionContainer()->token = $token;
    	
    	$info = $this->httpClientMock->getInfo();
    	
    	$this->assertFalse($info);
    	
    	$error = $this->httpClientMock->getError();
    	
        $this->assertSame('Get info return value is empty.', $error['internal-error']);
    
    }
    
    public function testFailNoTokenGetInfo()
    {        	
    	$this->httpClientMock->method('getHttpclientResponse')
    			->will($this->returnCallback(array($this, 'getMockedInfoResponse')));
    	
        $this->assertFalse($this->httpClientMock->getInfo());
    
        $error = $this->httpClientMock->getError();
        $this->assertSame('Session access token not found.', $error['internal-error']);
    
    }
    
    public function getMockedTokenResponse()
    {

        $response = new \Zend\Http\Response;

        $response->setContent('{
            "access_token": "ya29.AHES6ZQkpzzWwC6K3G6EHH-2s4DRVYCHSPwG",
            "token_type": "Bearer",
            "expires_in": 3600,
            "id_token": "eyJo_V3ftjOB4JnPlx7AXU8B6u5PKYNhkI6OSB0uEeE0x9aTjEm5q15Ukruxqrsk"
        }');

        return $response;

    }
    
    public function getFaultyMockedTokenResponse()
    {

        $response = new \Zend\Http\Response;

        $response->setContent('{"error": "some error"}');

        return $response;

    }
    
    public function getFaultyMockedNonJsonTokenResponse()
    {
    
        $response = new \Zend\Http\Response;
    
        $response->setContent('some=error+not+kul');
    
        return $response;
    
    }
    
    public function getMockedInfoResponse()
    {
    
        $response = '{
            "firstName": "John",
            "lastName": "Doe",
            "headline": "Inventor",
        	"pictureUrl": "pictureUrl",
        	"emailAddress": "JohnDoe@email.it"		
        }';
    
        //$response = new \Zend\Http\Response;
    
        //$response->setContent($content);
    
        return $response;
    
    }
    
    public function getMockedInfoResponseEmpty()
    {
        
        //$response = new \Zend\Http\Response;    
        //return $response;
        return false;
    
    }
    
}