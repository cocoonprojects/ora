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
        // Initialize of custom SUBCONTEXT     
        $this->useContext('SubContext_create_new_task', new SubContext_create_new_task(array(
                /* custom params */
        )));
    }
    
}