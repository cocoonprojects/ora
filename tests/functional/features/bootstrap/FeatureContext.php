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

    /**
     * @Given /^I am authenticated in ORA Project$/
     */
    public function iAmAuthenticatedInOraProject()
    {
        $sm = $this->getServiceManager();
        $return = $sm->get("Application\Service\LoginService")->login();

        return $return;
    }

    /**
     * @Given /^I am authenticated on ORA Project$/
     */
    public function iAmAuthenticatedOnOraProject()
    {
        $sm = $this->getServiceManager();
        $return = $sm->get("Application\Service\LoginService")->login();

        return $return;

    }

    /**
     * @When /^I get the list of ongoing tasks$/
     */
    public function iGetTheListOfOngoingTasks()
    {
        throw new PendingException();
    }

    /**
     * @Given /^the list of ongoing tasks is not empty$/
     */
    public function theListOfOngoingTasksIsNotEmpty()
    {
        throw new PendingException();
    }

    /**
     * @When /^I am task owner of a task$/
     */
    public function iAmTaskOwnerOfATask()
    {
        throw new PendingException();
    }

    /**
     * @Then /^I should see edit button$/
     */
    public function iShouldSeeEditButton()
    {
        throw new PendingException();
    }

    /**
     * @When /^I am not task owner of a task$/
     */
    public function iAmNotTaskOwnerOfATask()
    {
        throw new PendingException();
    }

    /**
     * @Then /^I should not see edit button$/
     */
    public function iShouldNotSeeEditButton()
    {
        throw new PendingException();
    }

    /**
     * @Then /^I should see delete button$/
     */
    public function iShouldSeeDeleteButton()
    {
        throw new PendingException();
    }

    /**
     * @Then /^I should not see delete  button$/
     */
    public function iShouldNotSeeDeleteButton()
    {
        throw new PendingException();
    }

    /**
     * @Given /^I am not authenticated in ORA Project$/
     */
    public function iAmNotAuthenticatedInOraProject()
    {
        throw new PendingException();
    }

    /**
     * @Given /^I am not authenticated in Google$/
     */
    public function iAmNotAuthenticatedInGoogle()
    {
        throw new PendingException();
    }

    /**
     * @When /^I am on ORA Project login form$/
     */
    public function iAmOnOraProjectLoginForm()
    {
        throw new PendingException();
    }

    /**
     * @Given /^I press "([^"]*)"$/
     */
    public function iPress($arg1)
    {
        throw new PendingException();
    }

    /**
     * @Given /^I fill an existing Google account$/
     */
    public function iFillAnExistingGoogleAccount()
    {
        throw new PendingException();
    }

    /**
     * @Then /^I should be logged in$/
     */
    public function iShouldBeLoggedIn()
    {
        throw new PendingException();
    }

    /**
     * @Given /^I am authenticated in Google$/
     */
    public function iAmAuthenticatedInGoogle()
    {
        throw new PendingException();
    }

}
