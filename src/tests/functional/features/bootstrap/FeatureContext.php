<?php

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Tester\Exception\PendingException;

/**
 * Behat context class.
 */
class FeatureContext implements SnippetAcceptingContext
{
    /**
     * Initializes context.
     *
     * Every scenario gets its own context object.
     * You can also pass arbitrary arguments to the context constructor through behat.yml.
     */
    public function __construct()
    {
    }

    /**
     * @Given there is something
     */
    public function thereIsSomething()
    {
        throw new PendingException();
    }

    /**
     * @When I do something
     */
    public function iDoSomething()
    {
        throw new PendingException();
    }

    /**
     * @Then I should see something
     */
    public function iShouldSeeSomething()
    {
        throw new PendingException();
    }

}
