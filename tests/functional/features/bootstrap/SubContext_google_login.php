<?php

//namespace Behat\MinkExtension\Context\MinkContext;
//use Behat\Behat\Context\BehatContext;
use Behat\MinkExtension\Context\MinkContext;

class SubContext_google_login extends MinkContext
{
    public function __construct(array $parameters)
    {
        
        // do subcontext initialization
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
     * @Given /^I don\'t see popup "([^"]*)"$/
     */
    public function iDonTSeePopup($arg1)
    {
    	$page = $this->getSession()->getPage();
    	$popup = $page->findById('popupLogin');
    	
    	return !$popup->isVisible();
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
    	//$page = $this->getSession()->getPage();
    	//$page->
    	//isVisible
    	//throw new PendingException();
    }
    
    

}