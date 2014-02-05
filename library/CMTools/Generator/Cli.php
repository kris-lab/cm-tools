<?php

class CMTools_Generator_Cli extends CM_Cli_Runnable_Abstract {

	/** @var CMTools_CodeGenerator_Php */
	protected $_generatorPhp;

	/** @var CMTools_CodeGenerator_Javascript */
	protected $_generatorJavascript;

	/** @var CMTools_CodeGenerator_Layout */
	protected $_generatorLayout;

	public function __construct(CM_InputStream_Interface $input = null, CM_OutputStream_Interface $output = null) {
		parent::__construct($input, $output);
		$this->_generatorPhp = new CMTools_CodeGenerator_Php();
		$this->_generatorJavascript = new CMTools_CodeGenerator_Javascript();
		$this->_generatorLayout = new CMTools_CodeGenerator_Layout();
	}

	/**
	 * @param string $className
	 * @throws CM_Exception_Invalid
	 */
	public function createView($className) {
		if (class_exists($className)) {
			throw new CM_Exception_Invalid('`' . $className . '` already exists');
		}
		$phpClassFile = $this->_generatorPhp->createClassFile($className);
		$this->_logFileCreation($phpClassFile);

		$jsClassFile = $this->_generatorJavascript->createClassFile($className);
		$this->_logFileCreation($jsClassFile);

		$templateFile = $this->_generatorLayout->createTemplateFile($className);
		$this->_logFileCreation($templateFile);

		$stylesheetFile = $this->_generatorLayout->createStylesheetFile($className);
		$this->_logFileCreation($stylesheetFile);
	}

	/**
	 * @param string $className
	 * @throws CM_Exception_Invalid
	 */
	public function createClass($className) {
		if (class_exists($className) && !$this->_getInput()->confirm('Class `' . $className . '` already exists. Replace?')) {
			return;
		}
		$file = $this->_generatorPhp->createClassFile($className);
		$this->_logFileCreation($file);
	}

	/**
	 * @param string $namespace
	 */
	public function createNamespace($namespace) {
		$this->_createNamespaceDirectories($namespace);
		CM_Bootloader::getInstance()->reloadNamespacePaths();
		$this->_generatorPhp->createClassFile($namespace . '_Site');

		$bootloaderClass = $this->_generatorPhp->createClass($namespace . '_Bootloader');
		$namespaces = array_merge(CM_Bootloader::getInstance()->getNamespaces(), array($namespace));
		$bootloaderClass->addMethod(new CG_Method('getNamespaces', "return array('" . implode("', '", $namespaces) . "');"));
		$this->_generatorPhp->createClassFileFromClass($bootloaderClass);
	}

	public function createJavascriptFiles() {
		$viewClasses = CM_View_Abstract::getClassChildren(true);
		foreach ($viewClasses as $path => $className) {
			if ($this->_isValidJavascriptView($className)) {
				$jsPath = preg_replace('/\.php$/', '.js', $path);
				if (!CM_File::exists($jsPath)) {
					$jsClassFile = $this->_generatorJavascript->createClassFile($className);
					$this->_logFileCreation($jsClassFile);
				}
			}
		}
	}

	/**
	 * @param string $className
	 * @return bool
	 */
	private function _isValidJavascriptView($className) {
		$invalidClassNameList = array('CM_Mail');
		foreach ($invalidClassNameList as $invalidClassName) {
			if ($className === $invalidClassName || is_subclass_of($className, $invalidClassName)) {
				return false;
			}
		}
		return true;
	}

	/**
	 * @param string $namespace
	 */
	private function _createNamespaceDirectories($namespace) {
		$paths = array();
		$paths[] = DIR_ROOT . 'library/' . $namespace . '/library/' . $namespace;
		$paths[] = DIR_ROOT . 'library/' . $namespace . '/layout/default';
		foreach ($paths as $path) {
			CM_Util::mkDir($path);
			$this->_getOutput()->writeln('Created `' . $path . '`');
		}
	}

	/**
	 * @param CM_File|null $file
	 */
	private function _logFileCreation(CM_File $file = null) {
		if ($file) {
			$this->_getOutput()->writeln('Created `' . $file->getPath() . '`');
		}
	}

	public static function getPackageName() {
		return 'generator';
	}
}
