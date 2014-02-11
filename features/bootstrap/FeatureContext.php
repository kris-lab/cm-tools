<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

//
// Require 3rd-party libraries here:
//
//   require_once 'PHPUnit/Autoload.php';
//   require_once 'PHPUnit/Framework/Assert/Functions.php';
//

/**
 * Features context.
 */
class FeatureContext extends BehatContext
{
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

//
// Place your definition and hook methods here:
//
//    /**
//     * @Given /^I have done something with "([^"]*)"$/
//     */
//    public function iHaveDoneSomethingWith($argument)
//    {
//        doSomethingWith($argument);
//    }//

    /**
     * @Given /^file "([^"]*)" does not exist$/
     */
    public function fileDoesNotExist($file)
    {
        if(CM_File::exists($file)) {
           $file = new CM_File($file);
            $file->delete();
        }
    }

    /**
     * @Then /^file "([^"]*)" exists$/
     */
    public function fileExists($file)
    {
        if(!CM_File::exists($file)) {
            throw new Exception("File does not exist!");
        }
    }

    /**
     * @When /^I run "([^"]*)"$/
     */
    public function iRun($command)
    {
        CM_Util::exec($command);
    }

}

