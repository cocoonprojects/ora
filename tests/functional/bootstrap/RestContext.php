<?php

use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Mink\Driver\BrowserKitDriver;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use Behat\Testwork\Hook\Scope\AfterSuiteScope;
use Guzzle\Plugin\Cookie\Cookie;
use Guzzle\Plugin\Cookie\CookieJar\ArrayCookieJar;
use Guzzle\Plugin\Cookie\CookiePlugin;
use Guzzle\Service\Client;

/**
 * Rest context.
 */
class RestContext extends RawMinkContext
{
	private $_restObject = null;
	private $_restObjectType = null;
	private $_restObjectMethod = 'get';
	private $_client = null;
	private $_response = null;
	private $_requestUrl = null;
	private $base_url = null;

	private static $LOGIN_URL = '/auth/login/acceptance';
	private $json = null;
	private $jsonProperties = null;
	
	/**
	 *  @BeforeSuite
	 */
	public static function setupApplication(BeforeSuiteScope $scope){
		if(putenv('APPLICATION_ENV=acceptance')) {
			echo "APPLICATION_ENV=" . getenv('APPLICATION_ENV') . " set successfully!\n";
		} else {
			echo 'Setting APPLICATION_ENV=acceptance failed!';
			die();
		}
		
		echo shell_exec('../src/vendor/bin/doctrine-module orm:schema-tool:drop --force');
		echo shell_exec('../src/vendor/bin/doctrine-module orm:schema-tool:create');
		echo shell_exec('../src/vendor/bin/doctrine-module dbal:import ' . __DIR__ . '/../../sql/init.sql');
	}
	
	/** @AfterSuite */
	public static function teardownApplication(AfterSuiteScope $scope){
		echo shell_exec('../src/vendor/bin/doctrine-module orm:schema-tool:drop --force');
	} 
	
	/**
	 * Initializes context.
	 * Every scenario gets it's own context object.
	 */
	public function __construct($base_url)
	{
		// Initialize your context here
		$this->_restObject = new stdClass();
		$this->_client = new Client();
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
		$this->iRequest(self::$LOGIN_URL);
		if($this->_response->getStatusCode() != 200) {
			throw new \Exception('Cannot authenticate '.$email.' user: response status code for url '. $this->_requestUrl . ' is '.$this->_response->getStatusCode());
		}
		unset($this->_restObject->email);
		
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
	 * @Then /^the response should be JSON$/
	 */
	public function theResponseShouldBeJson()
	{
		if(empty($this->json)) {
			$this->json = json_decode($this->_response->getBody(true));
			if (empty($this->json)) {
				throw new Exception("Response was not JSON\n" . $this->_response->getBody(true));
			}
		}
		return $this->json;
	}
	
	/**
	 * @Then /^the "([^"]*)" property should be "([^"]*)"$/
	 */
	
	public function thePropertyShoudlBe($propertyName, $value){
		$this->theResponseShouldHaveAProperty($propertyName);
		if($value == 'null' && !is_null($this->json->$propertyName)) {
			throw new Exception($propertyName . " property value is not ".$value.". It is ".$this->json->$propertyName);
	   	} elseif ($this->json->$propertyName != $value) {
			throw new Exception($propertyName . " property value is not equal to ".$value.". It is ".$this->json->$propertyName); 
		}
	}
	
	/**
	 * @Then /^the "([^"]*)" property size should be "([^"]*)"$/
	 */
	public function thePropertySizeShouldBe($propertyName, $value)
	{
		$this->theResponseShouldHaveAProperty($propertyName);
		
		if (is_array($this->json->$propertyName)) {
			if (count($this->json->$propertyName) != $value) {
				throw new \Exception('Property size isn\'t equal to ' . $value .'! It is ' . count($this->json->$propertyName));
			}
		} elseif (count(get_object_vars($this->json->$propertyName)) != $value) {
			throw new \Exception('Property size isn\'t equal to ' . $value .'! It is ' . count($this->json->$propertyName));

		}
	}

	/**
	 * @Then /^the "([^"]*)" property size should be greater or equal than "([^"]*)"$/
	 */
	public function thePropertySizeShouldBeGreaterOrEqualThan($propertyName, $value)
	{
		$this->theResponseShouldHaveAProperty($propertyName);
		if (is_array($this->json->$propertyName)) {
			if (count($this->json->$propertyName) < $value) {
				throw new \Exception('Property size isn\'t greater or equal than '.$value .'! It is ' . count($this->json->$propertyName));
			}
		} elseif (count(get_object_vars($this->json->$propertyName)) < $value) {
			throw new \Exception('Property size isn\'t greater or equal than '.$value .'! It is ' . count($this->json->$propertyName));
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
	 * @Then /^the header "([^"]*)" should be "([^"]*)"$/
	 */
	public function theHeaderShouldBe($header, $value)
	{
		
	}

	/**
	 * @Then /^the response should have a "([^"]*)" property$/
	 */
	public function theResponseShouldHaveAProperty($propertyName)
	{
		$properties = $this->getJsonProperties();
		if(substr($propertyName, 0, 1) === '/' && substr($propertyName, -1) === '/') {
			$filtered = array_filter($properties, function ($value) use ($propertyName) {
				if(preg_match($propertyName, $value)) {
					return $value;
				}
			});
			if(count($filtered) == 0) {
				throw new Exception("Property '" . $propertyName ."' does not exist in response " . implode(', ', $properties));
			}
		} elseif (! in_array($propertyName, $properties)) {
			throw new Exception("Property '" . $propertyName ."' does not exist in response " . implode(', ', $properties));
		}
	}
	
	/**
	 * @Then /^the response shouldn't have a "([^"]*)" property$/
	 */
	public function theResponseShouldntHaveAProperty($propertyName)
	{
		$properties = $this->getJsonProperties();
		if(substr($propertyName, 0, 1) === '/' && substr($propertyName, -1) === '/') {
			$filtered = array_filter($properties, function ($value) use ($propertyName) {
				if(preg_match($propertyName, $value)) {
					return $value;
				}
			});
			if(count($filtered) > 0) {
				throw new Exception("Property '" . $propertyName ."' exists in response " . implode(', ', $filtered));
			}
		} elseif (in_array($propertyName, $properties)) {
			throw new Exception("Property '" . $propertyName ."' exists in response ");
		}
	}
	
	/**
	 * @Then /^echo last response$/
	 */
	public function echoLastResponse()
	{
		print_r($this->_requestUrl);
		print_r($this->_response->getBody(true));
	}
	
	protected function getResponse()
	{
		return $this->_response;
	}
	
	protected function getJsonProperties()
	{
		if(is_null($this->jsonProperties)) {
			$json = $this->theResponseShouldBeJson();
			$iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($json), RecursiveIteratorIterator::SELF_FIRST);
			$this->jsonProperties = array();
			foreach ($iterator as $key => $value) {
				// Build long key name based on parent keys
				for ($i = $iterator->getDepth() - 1; $i >= 0; $i--) {
					$key = $iterator->getSubIterator($i)->key() . '.' . $key;
				}
				$this->jsonProperties[] = $key;
			}
		}
		return $this->jsonProperties;
	}
}
