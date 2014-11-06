<?php

use Behat\Behat\Context\Context;

use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Mink\Driver\BrowserKitDriver;
use Behat\Mink\Exception\UnsupportedDriverActionException;

use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use Behat\Testwork\Hook\Scope\AfterSuiteScope;

use Zend\EventManager\EventManager;
use Zend\Http\PhpEnvironment;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\Application;
use Zend\ServiceManager\ServiceManager;
use Zend\Db\Adapter\Adapter as ZendDbAdapter;


/**
 * Rest context.
 */
class RestContext extends RawMinkContext implements Context
{
    private $_restObject = null;
    private $_restObjectType = null;
    private $_restObjectMethod = 'get';
    private $_client = null;
    private $_response = null;
    private $_requestUrl = null;
    private $base_url = null;

    /** @var \Zend\Mvc\Application */
    private static $zendApp;
    /** @var Doctrine\ORM\Tools\SchemaTool  */
	private static $schemaTool;
    
	private static $entityManager;
	
	/**
     *  @BeforeSuite
     */
    public static function setupApplication(BeforeSuiteScope $scope){
		    			
		putenv('APPLICATION_ENV=acceptance');
    	
		$path_config = __DIR__.'/../../../src/config/application.config.php';	 	
		$path = __DIR__.'/../../../src/vendor/zendframework/zendframework/library';		
        putenv("ZF2_PATH=".$path);

	 	include '/vagrant/src/init_autoloader.php';
        self::$zendApp = Zend\Mvc\Application::init(require $path_config);	
	
        $sm = self::$zendApp->getServiceManager();
		
		self::$entityManager = $sm->get('doctrine.entitymanager.orm_default');

		self::$schemaTool = new \Doctrine\ORM\Tools\SchemaTool(self::$entityManager);
		
		//posso recuperare eventualmente solo le entità che mi interessano
//		$classes = array(
//		  self::$entityManager->getClassMetadata('Entities\User'),
//		  self::$entityManager->getClassMetadata('Entities\Profile')
//		);
		
		$classes = self::$entityManager->getMetadataFactory()->getAllMetadata();

		self::deleteDatabase(); //serve solo la prima volta che sono eseguiti i test, se il database non e' stato svuotato
		
		self::$schemaTool->createSchema($classes);
		
		//recupero la query sql per creare la tabella event_store
		$sql = file_get_contents("/vagrant/src/vendor/prooph/event-store-zf2-adapter/scripts/mysql-single-stream-default-schema.sql");
		//eseguo la query sul database
		$statement = self::$entityManager->getConnection()->prepare($sql);		
		$statement->execute();
		$statement->closeCursor();
		
		//recupero la query sql per popolare il database con i dati di test	
		$sql = file_get_contents("/vagrant/tests/sql/init.sql");
		//eseguo la query sul database
		$statement = self::$entityManager->getConnection()->executeUpdate($sql, array(), array());		
		
    }
    
    /** @AfterSuite */
	public static function teardownApplication(AfterSuiteScope $scope){
				
		self::deleteDatabase();
		
	} 
    
	private static function deleteDatabase(){
		
		//cancello il database con doctrine
		$classes = self::$entityManager->getMetadataFactory()->getAllMetadata();
		self::$schemaTool->dropSchema($classes);	

		//cancello la tabella event_stream
		$sql_drop_event_store = "drop table event_stream";
		$statement_del = self::$entityManager->getConnection()->executeUpdate($sql_drop_event_store, array(), array());
		
	}
	
    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     */
    public function __construct($base_url)
    {
        // Initialize your context here
        $this->_restObject = new stdClass();
        $this->_client = new Guzzle\Service\Client();
        $this->base_url = $base_url;               
    }

    public function getBaseUrl()
    {
    	$driver = $this->getSession()->getDriver();
    	$driver->setCookie(session_name(), session_id());
    	$session = new \Behat\Mink\Session($driver);
    	$session->start();
    	
        if ($this->base_url === "" || $this->base_url === null) 
        {            
            throw new \Exception('Base_url not loaded!');
        }
        else 
        {
            return (isset($this->base_url)) ? $this->base_url : null;
        }
    }

    /**
     * @Given /^that I want to make a new "([^"]*)"$/
     */
    public function thatIWantToMakeANew($objectType)
    {    	
        $this->_restObjectType = ucwords(strtolower($objectType));
        $this->_restObjectMethod = 'post';
    }

    /**
     * @Given /^that I want to update a "([^"]*)"$/
     */
    public function thatIWantToUpdateA($objectType)
    {
        $this->_restObjectType = ucwords(strtolower($objectType));
        $this->_restObjectMethod = 'put';
    }
    
    /**
     * @Given /^that I want to find a "([^"]*)"$/
     */
    public function thatIWantToFindA($objectType)
    {
        $this->_restObjectType = ucwords(strtolower($objectType));
        $this->_restObjectMethod = 'get';
    }

    /**
     * @Given /^that I want to delete a "([^"]*)"$/
     */
    public function thatIWantToDeleteA($objectType)
    {
        $this->_restObjectType = ucwords(strtolower($objectType));
        $this->_restObjectMethod = 'delete';
    }

    /**
     * @Given /^that its "([^"]*)" is "([^"]*)"$/
     */
    public function thatTheItsIs($propertyName, $propertyValue)
    {
        $this->_restObject->$propertyName = $propertyValue;
    }

    /**
     * @Given /^that I am authenticated as "([^"]*)"$/
     */
    public function thatIAmAuthenticatedAs($email)
    {
    	$this->thatTheItsIs('email', $email);
    	$this->_restObjectMethod = 'post';
    	$this->iRequest('/auth/acceptanceLogin');
    	if($this->_response->getStatusCode() != 200) {
    		throw new \Exception('Cannot authenticate '.$email.' user');
    	}
    	
    	$setCookie = $this->_response->getSetCookie();
    	
    	//echo "set cookie: ".$setCookie."\n";
    	$phpsessid = null;
    	
    	// PHPSESSID=p3sp0qs8ai1c62o9ll9o18ro20; path=/ 
    	if(strpos($setCookie, ';') !== false)
    	{
    		//echo "punto e virgola ; \n";
    		$tmp = explode(';', $setCookie);

    		if(strpos($tmp[0], 'PHPSESSID') !== false)
    		{
    			//echo "PHPSESSID c'e \n";
				list($nameCookie, $phpsessid) = explode('=', $tmp[0]);
    		}
    	}
    	
    	//echo "PHPSESSID: ".$phpsessid;
    	$cookie = new Guzzle\Plugin\Cookie\Cookie();
    	$cookie->setName('PHPSESSID');
    	$cookie->setPath('/');
    	
    	$cookie->setValue($phpsessid);
    	    	
    	$domain = trim(str_replace("http://", "", $this->base_url));    	
    	$cookie->setDomain($domain);
    	
    	$jar = new Guzzle\Plugin\Cookie\CookieJar\ArrayCookieJar();
    	$jar->add($cookie);
    	$plugin = new Guzzle\Plugin\Cookie\CookiePlugin($jar);
    	
    	$this->_client->addSubscriber($plugin);
    }
    
    /**
     * @When /^I request "([^"]*)"$/
     */
    public function iRequest($pageUrl)
    {
        $baseUrl = $this->getBaseUrl();
        $this->_requestUrl = $baseUrl . $pageUrl;
        
        
        //$this->_client->addCookie('PHPSESSID',$this->_phpsessid);
        
        switch (strtoupper($this->_restObjectMethod)) 
        {        	
            case 'GET':
                // Create a GET request: $client->get($uri, array $headers, $options)
                try
                {
                    $response = $this->_client->get(
                        $this->_requestUrl . '?' . http_build_query((array) $this->_restObject)
                    )->send();
                }
                catch (Guzzle\Http\Exception\ClientErrorResponseException $e) {
                    $response = $e->getResponse();
                }
                catch (Guzzle\Http\Exception\ServerErrorResponseException $e) {
                    $response = $e->getResponse();
                }
                catch (Guzzle\Http\Exception\BadResponseException $e) {
                    $response = $e->getResponse();
                }
                catch( Exception $e){
                    throw new Exception($e->getMessage());
                }
                break;
            
            case 'PUT':
                // Create a PUT request: $client->put($uri, array $headers, $body, $options)
                try 
                {
                    $postFields = (array) $this->_restObject;
                    $response = $this->_client->put(
                        $this->_requestUrl, null, $postFields
                    )->send();
                }
                catch (Guzzle\Http\Exception\ClientErrorResponseException $e) {
                    $response = $e->getResponse();
                }
                catch (Guzzle\Http\Exception\ServerErrorResponseException $e) {
                    $response = $e->getResponse();
                }
                catch (Guzzle\Http\Exception\BadResponseException $e) {
                    $response = $e->getResponse();
                }
                catch( Exception $e){
                    throw new Exception($e->getMessage());
                }
                break;
                
            case 'POST': 
                // Create a POST request: $client->post($uri, array $headers, $postBody, $options)
                try
                {             
                    $postFields = (array) $this->_restObject;
                    $response = $this->_client->post(
                        $this->_requestUrl, null, $postFields
                    )->send();
                }
                catch (Guzzle\Http\Exception\ClientErrorResponseException $e) {
                    $response = $e->getResponse();
                }
                catch (Guzzle\Http\Exception\ServerErrorResponseException $e) {
                	echo $e->getMessage();
                    $response = $e->getResponse();
                }
                catch (Guzzle\Http\Exception\BadResponseException $e) {
                    $response = $e->getResponse();
                }
                catch( Exception $e){
                    throw new Exception($e->getMessage());
                }
                break;
                
            case 'DELETE':
                // Create a DELETE request: $client->delete($uri, array $headers, $body, $options)
                try
                {   
                    $response = $this->_client->delete(
                        $this->_requestUrl . '?' . http_build_query((array) $this->_restObject)
                    )->send();
                }
                catch (Guzzle\Http\Exception\ClientErrorResponseException $e) {
                    $response = $e->getResponse();
                }
                catch (Guzzle\Http\Exception\ServerErrorResponseException $e) {
                    $response = $e->getResponse();
                }
                catch (Guzzle\Http\Exception\BadResponseException $e) {
                    $response = $e->getResponse();
                }
                catch( Exception $e){
                    throw new Exception($e->getMessage());
                }
                break;
             
            default:
                throw new Exception("_restObjectMethod NOT MANAGED!");
        }

        $this->_response = $response;
    }

    /**
     * @Then /^the response is JSON$/
     */
    public function theResponseIsJson()
    {
        $data = json_decode($this->_response->getBody(true));
        
        if (empty($data)) {
            throw new Exception("Response was not JSON\n" . $this->_response);
        }
    }

    /**
     * @Given /^the response has a "([^"]*)" property$/
     */
    public function theResponseHasAProperty($propertyName)
    {
        $data = json_decode($this->_response->getBody(true));
        
        if (! empty($data)) {
            if (! isset($data->$propertyName)) {
                throw new Exception("Property '" . $propertyName .
                         "' is not set!\n");
            }
        } else {
            throw new Exception(
                    "Response was not JSON\n" . $this->_response->getBody(true));
        }
    }

    /**
     * @Then /^the "([^"]*)" property equals "([^"]*)"$/
     */
    public function thePropertyEquals($propertyName, $propertyValue)
    {
        $data = json_decode($this->_response->getBody(true));
        
        if (! empty($data)) {
            if (! isset($data->$propertyName)) {
                throw new Exception("Property '" . $propertyName .
                         "' is not set!\n");
            }
            if ($data->$propertyName !== $propertyValue) {
                throw new \Exception(
                        'Property value mismatch! (given: ' . $propertyValue .
                                 ', match: ' . $data->$propertyName . ')');
            }
        } else {
            throw new Exception(
                    "Response was not JSON\n" . $this->_response->getBody(true));
        }
    }

    /**
     * @Given /^the type of the "([^"]*)" property is ([^"]*)$/
     */
    public function theTypeOfThePropertyIsNumeric($propertyName, $typeString)
    {
        $data = json_decode($this->_response->getBody(true));
        
        if (! empty($data)) 
        {
            if (! isset($data->$propertyName)) 
            {
                throw new Exception("Property '" . $propertyName .
                         "' is not set!\n");
            }
            // check our type
            switch (strtolower($typeString)) 
            {
                case 'numeric':
                    if (! is_numeric($data->$propertyName)) 
                    {
                        throw new Exception(
                                "Property '" . $propertyName .
                                         "' is not of the correct type: " .
                                         $theTypeOfThePropertyIsNumeric . "!\n");
                    }
                break;
            }
        } 
        else 
        {
            throw new Exception(
                    "Response was not JSON\n" . $this->_response->getBody(true));
        }
    }

    /**
     * @Then /^the response status code should be (\d+)$/
     */
    public function theResponseStatusCodeShouldBe($httpStatus)
    {
        if ((string) $this->_response->getStatusCode() !== $httpStatus) 
        {
            throw new \Exception(
                    'HTTP code does not match ' . $httpStatus . ' (actual: ' .
                             $this->_response->getStatusCode() . ')');
        }
    }

    /**
     * @Then /^echo last response$/
     */
    public function echoLastResponse()
    {
    	//print_r($this->_requestUrl);
    	//print_r($this->_response);
    	echo "PHPSESSID da debug". $this->_response->getSetCookie('');
        //$this->printDebug($this->_requestUrl . "\n\n" . $this->_response);
    }

}
