<?php


use Behat\Behat\Context\BehatContext;
use Behat\MinkExtension\Context\MinkDictionary;

class FeatureContext extends BehatContext
{
    use MinkDictionary;

    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {       
        // Initialize your context here       
    }

    
    /**
     * @Then /^I wait for the suggestion box to appear$/
     */
    /*
    public function iWaitForTheSuggestionBoxToAppear()
    {
        $this->getSession()->wait(5000, "$('.suggestions-results').children().length > 0");
    }
    */
    
}