<?php

use Behat\Behat\Context\BehatContext;

/**
 * Features context.
 */
class FeatureContext extends BehatContext {
	/**
	 * @Given /^file "([^"]*)" does not exist$/
	 */
	public function fileDoesNotExist($file) {
		if (CM_File::exists($file)) {
			$file = new CM_File($file);
			$file->delete();
		}
	}

	/**
	 * @Then /^file "([^"]*)" exists$/
	 */
	public function fileExists($file) {
		if (!CM_File::exists($file)) {
			throw new Exception("File does not exist!");
		}
	}

	/**
	 * @When /^I run "([^"]*)"$/
	 */
	public function iRun($command) {
		CM_Util::exec($command);
	}
}

