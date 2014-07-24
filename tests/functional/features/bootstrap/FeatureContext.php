<?php

use Behat\Behat\Context\BehatContext;
use Behat\Behat\Exception\PendingException;

/**
 * Features context.
 */
class FeatureContext extends BehatContext
{
    /** @var \Zend\Mvc\Application */
    private static $zendApp;

    /** @BeforeSuite */
    static public function initializeZendFramework()
    {
        if(self::$zendApp === null)
        {
            $path_config = '/vagrant/tests/unit/test.config.php';

            $path = '/vagrant/vendor/zendframework/zendframework/library';
            putenv("ZF2_PATH=".$path);

            include '/vagrant/src/init_autoloader.php';

            self::$zendApp = Zend\Mvc\Application::init(require $path_config);
        }
    }

    private function getServiceManager()
    {
        return self::$zendApp->getServiceManager();
    }

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
     * @Given /^there is something$/
     */
    public function thereIsSomething()
    {
        throw new PendingException();
    }

    /**
     * @When /^I do something$/
     */
    public function iDoSomething()
    {
        throw new PendingException();
    }

    /**
     * @Then /^I should see something$/
     */
    public function iShouldSeeSomething()
    {
        throw new PendingException();
    }

}
