<?php 

use Behat\Behat\Context\Context;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Behat\Context\CustomSnippetAcceptingContext;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

class GoogleLoginContext extends MinkContext implements Context
{
    public function __construct()
    {
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
}