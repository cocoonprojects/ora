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
use Guzzle\Plugin\Cookie\Cookie;
use Guzzle\Plugin\Cookie\CookieJar\ArrayCookieJar;
use Guzzle\Plugin\Cookie\CookiePlugin;


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
		    			
    	echo "Setting up application...\n";
    	
		putenv('APPLICATION_ENV=acceptance');
    	
		$path_config = __DIR__.'/../../../src/config/application.config.php';	 	
		$path = __DIR__.'/../../../src/vendor/zendframework/zendframework/library';		
        putenv("ZF2_PATH=".$path);

	 	include __DIR__.'/../../../src/init_autoloader.php';
        self::$zendApp = Zend\Mvc\Application::init(require $path_config); //new application instance
	
        $sm = self::$zendApp->getServiceManager();		
		self::$entityManager = $sm->get('doctrine.entitymanager.orm_default');
		self::$schemaTool = new \Doctrine\ORM\Tools\SchemaTool(self::$entityManager);
				
		self::deleteDatabase(); //useful at the very first execution of this function 
		
		//get all doctrine metadata for create schema
		$classes = self::$entityManager->getMetadataFactory()->getAllMetadata();
		self::$schemaTool->createSchema($classes);
		
		//get query for event_store table creation
		$sql = file_get_contents(__DIR__.'/../../../src/vendor/prooph/event-store-zf2-adapter/scripts/mysql-single-stream-default-schema.sql');

		$statement = self::$entityManager->getConnection()->prepare($sql);		
		$statement->execute();
		$statement->closeCursor(); //needed for mysql database
		
		//get query for test data
		$sql = file_get_contents(__DIR__.'/../../../tests/sql/init.sql');
		$statement = self::$entityManager->getConnection()->executeUpdate($sql, array(), array());		
		
		
		echo "...done!\n";
    }
    
    /** @AfterSuite */
	public static function teardownApplication(AfterSuiteScope $scope){

		echo "Tear down application...\n";
		
		self::deleteDatabase();
		
		echo "...done!\n";
		
	} 
    
	private static function deleteDatabase(){
		
		//drop tables creates by doctrine
		$classes = self::$entityManager->getMetadataFactory()->getAllMetadata();
		self::$schemaTool->dropSchema($classes);	

		//drop event_stream table
		$sql_drop_event_store = "drop table if exists event_stream";
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
            throw new \Exception("Base_url not loaded!");
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
    	
    	$cookie = $this->_response->getSetCookie();
    	// PHPSESSID=p3sp0qs8ai1c62o9ll9o18ro20; path=/ 
    	$tmp = explode(';', $cookie);

    	$phpsessid = null;
    	if(strpos($tmp[0], 'PHPSESSID') !== false)
    	{
			list($nameCookie, $phpsessid) = explode('=', $tmp[0]);
    	}
    	 
    	//echo "PHPSESSID: ".$phpsessid;
    	$cookie = new Cookie();
    	$cookie->setName('PHPSESSID');
    	$cookie->setPath('/');
    	$cookie->setValue($phpsessid);
    	$domain = trim(str_replace("http://", "", $this->base_url));    	
    	$cookie->setDomain($domain);
    	
    	$jar = new ArrayCookieJar();
    	$jar->add($cookie);
    	$plugin = new CookiePlugin($jar);
    	
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
    	print_r($this->_requestUrl);
    	print_r($this->_response->getBody(true));
//     	echo "PHPSESSID da debug". $this->_response->getSetCookie('');
//         $this->printDebug($this->_requestUrl . "\n\n" . $this->_response);
    }
    
    
    /**
     * @Given /^the array "([^"]*)" in JSON response has elements with "([^"]*)" property$/
     */
    public function theJsonResponseHasElementsWithAProperty($arrayName, $propertyElement)
    {
    
    	$data = json_decode($this->_response->getBody(true));
    	if (! empty($data->$arrayName)) {
    		foreach($data->$arrayName as $element){
    			if (! isset($element->$propertyElement)) {
    				throw new Exception("Property '" . $propertyElement . "' is not set in elements of {$arrayName} array!\n");
    			}
    		}
    	} else {
    		throw new Exception(
    				"Response was not JSON\n" . $this->_response->getBody(true));
    	}
    }
    
    /**
     * @Then /^the value of property "([^"]*)" of the element with property "([^"]*)" with value "([^"]*)" in array "([^"]*)" should be "([^"]*)"$/
     */
    
    public function theValueOfPropertyOfTheElementWithPropertyWithInArrayShoudlBe($propertyNameToCheck,$propertyIdName, $propertyIdValue,$arrayName,$resultProperty){
    	$count =0;
    	$data = json_decode($this->_response->getBody(true));
    	if (! empty($data->$arrayName)) {
    		foreach($data->$arrayName as $element){
    			if ($element->$propertyIdName==$propertyIdValue) {
    				$count++;
    				if($element->$propertyNameToCheck!=$resultProperty)
    					throw new Exception("Property ".$propertyNameToCheck." value that is ".$element->$propertyNameToCheck."is not equal to desired value ".$resultProperty); 
    			}
    		}
    		if ($count==0)
    			throw new Exception("Cannot Finde an element with ".$propertyIdName." in the array ".$arrayName);
    		
    	} else {
    		throw new Exception(
    				"Response was not JSON\n" . $this->_response->getBody(true));
    	}
    	
    }
    
    
	protected function getResponse(){
    	return $this->_response;
    }
}
