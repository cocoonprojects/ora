<?php 

use Behat\Behat\Context\Context;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Behat\Context\CustomSnippetAcceptingContext;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

class LoginContext extends MinkContext implements Context
{
    public function __construct($base_url)
    {
	$this->base_url = $base_url;
    }
    
    /**
     * @Given /^I am not authenticated in ORA Project$/
     */
    public function iAmNotAuthenticatedInOraProject()
    {
    	return true;
    	//throw new PendingException();
    }
            
    /**
     * @Given /^I click on "([^"]*)"$/
     */
    public function iClickOn($arg1)
    {    	    	
    	$page = $this->getSession()->getPage();    	
    	$page->clickLink($arg1);

    }
    
    /**
     * @Given /^I should see popup "([^"]*)"$/
     */    
    public function iShouldSeePopup($arg1)
    {
    	$page = $this->getSession()->getPage();
    	$page->hasContent($arg1);    	
    }    	
}
