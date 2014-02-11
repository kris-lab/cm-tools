<?php

class CMTools_Generator_Cli extends CM_Cli_Runnable_Abstract {

	/** @var CMTools_AppInstallation */
	protected $_appInstallation;

	/** @var CMTools_Generator_Class_Php */
	protected $_generatorPhp;

	/** @var CMTools_Generator_Class_Javascript */
	protected $_generatorJavascript;

	/** @var CMTools_Generator_Class_Layout */
	protected $_generatorLayout;

	/** @var CMTools_Generator_Application */
	protected $_generatorApp;

	public function __construct(CM_InputStream_Interface $input = null, CM_OutputStream_Interface $output = null) {
		parent::__construct($input, $output);
		$this->_appInstallation = new CMTools_AppInstallation();
		$this->_generatorPhp = new CMTools_Generator_Class_Php($this->_appInstallation, $this->_getOutput());
		$this->_generatorJavascript = new CMTools_Generator_Class_Javascript($this->_appInstallation, $this->_getOutput());
		$this->_generatorLayout = new CMTools_Generator_Class_Layout($this->_appInstallation, $this->_getOutput());
		$this->_generatorApp = new CMTools_Generator_Application($this->_appInstallation, $this->_getOutput());
	}

	/**
	 * @param string $className
	 * @throws CM_Exception_Invalid
	 */
	public function createView($className) {
		$this->_generatorPhp->createClassFile($className);
		$this->_generatorJavascript->createClassFile($className);
		$this->_generatorLayout->createTemplateFile($className);
		$this->_generatorLayout->createStylesheetFile($className);
	}

	/**
	 * @param string $className
	 * @throws CM_Exception_Invalid
	 */
	public function createClass($className) {
		if (class_exists($className) && !$this->_getInput()->confirm('Class `' . $className . '` already exists. Replace?')) {
			return;
		}
		$this->_generatorPhp->createClassFile($className);
	}

	/**
	 * @param string       $moduleName
	 * @param boolean|null $singleModuleStructure
	 * @param string|null  $modulePath
	 * @throws CM_Cli_Exception_Internal
	 */
	public function createModule($moduleName, $singleModuleStructure = null, $modulePath = null) {
		if ($this->_appInstallation->moduleExists($moduleName)) {
			throw new CM_Cli_Exception_Internal('Module `' . $moduleName . '` already exists');
		}

		if ($singleModuleStructure) {
			if (count($this->_appInstallation->getModules()) > 0) {
				throw new CM_Cli_Exception_Internal('Cannot create new `single-module-structure` module when some modules already exists');
			}
			if (null !== $modulePath) {
				throw new CM_Cli_Exception_Internal('Cannot specify `module-path` when using `single-module-structure`');
			}
			$modulePath = '';
		} else {
			if ($this->_appInstallation->isSingleModuleStructure()) {
				throw new CM_Cli_Exception_Internal('Cannot add more modules to `single-module-structure` package');
			}
			if (null === $modulePath) {
				$modulePath = $this->_appInstallation->getModulesPath() . $moduleName;
			}
			if (null == $modulePath) {
				throw new CM_Cli_Exception_Internal('Cannot find module path');
			}
		}
		$this->_generatorApp->addModule($moduleName, $modulePath);
	}

	/**
	 * @param string $moduleName
	 * @param string $namespace
	 * @throws CM_Cli_Exception_Internal
	 */
	public function createNamespace($moduleName, $namespace) {
		if (!$this->_appInstallation->moduleExists($moduleName)) {
			throw new CM_Cli_Exception_Internal('Module `' . $moduleName . '` must exist! Existing modules: ' . join(', ', $modules));
		}
		/** @var \Composer\Autoload\ClassLoader $autoloader */
		$autoLoader = include DIR_ROOT . 'vendor/autoload.php';
		$namespacePrefixes = $autoLoader->getPrefixes();
		if (array_key_exists($namespace . '_', $namespacePrefixes)) {
			throw new CM_Cli_Exception_Internal('Namespace `' . $namespace . '` already exists');
		}
		$namespacePath = $this->_appInstallation->getModulePath($moduleName)  . 'library/' . $namespace;
		$this->_generatorApp->addNamespace($namespace, $namespacePath);
	}

	public function createJavascriptFiles() {
		$viewClasses = CM_View_Abstract::getClassChildren(true);
		foreach ($viewClasses as $path => $className) {
			if ($this->_isValidJavascriptView($className)) {
				$jsPath = preg_replace('/\.php$/', '.js', $path);
				if (!CM_File::exists($jsPath)) {
					$this->_generatorJavascript->createClassFile($className);
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

	public static function getPackageName() {
		return 'generator';
	}
}
