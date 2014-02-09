<?php

class CMTools_Generator_Cli extends CM_Cli_Runnable_Abstract {

	/** @var CMTools_Generator_Class_Php */
	protected $_generatorPhp;

	/** @var CMTools_Generator_Class_Javascript */
	protected $_generatorJavascript;

	/** @var CMTools_Generator_Class_Layout */
	protected $_generatorLayout;

	public function __construct(CM_InputStream_Interface $input = null, CM_OutputStream_Interface $output = null) {
		parent::__construct($input, $output);
		$this->_generatorPhp = new CMTools_Generator_Class_Php();
		$this->_generatorJavascript = new CMTools_Generator_Class_Javascript();
		$this->_generatorLayout = new CMTools_Generator_Class_Layout();
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
	 * @param string       $moduleName
	 * @param boolean|null $singleModuleStructure
	 * @param string|null  $modulePath
	 * @throws CM_Cli_Exception_Internal
	 */
	public function createModule($moduleName, $singleModuleStructure = null, $modulePath = null) {
		$appInstallation = new CMTools_AppInstallation();
		if ($appInstallation->moduleExists($moduleName)) {
			throw new CM_Cli_Exception_Internal('Module `' . $moduleName . '` already exists');
		}

		if ($singleModuleStructure) {
			if (count($appInstallation->getModules()) > 0) {
				throw new CM_Cli_Exception_Internal('Cannot create new `single-module-structure` module when some modules already exists');
			}
			if (null !== $modulePath) {
				throw new CM_Cli_Exception_Internal('Cannot specify `module-path` when using `single-module-structure`');
			}
			$modulePath = '';
		} else {
			if ($appInstallation->isSingleModuleStructure()) {
				throw new CM_Cli_Exception_Internal('Cannot add more modules to `single-module-structure` package');
			}
			if (null === $modulePath) {
				$modulePath = $appInstallation->getModulesPath() . $moduleName;
			}
			if (null == $modulePath) {
				throw new CM_Cli_Exception_Internal('Cannot find module path');
			}
		}
		$builder = new CMTools_Generator_Builder($appInstallation, $this->_getOutput());
		$builder->addModule($moduleName, $modulePath);
	}

	/**
	 * @param string $moduleName
	 * @param string $namespace
	 * @throws CM_Cli_Exception_Internal
	 */
	public function createNamespace($moduleName, $namespace) {
		$modules = CM_Bootloader::getInstance()->getNamespaces();
		if (!in_array($moduleName, $modules)) {
			throw new CM_Cli_Exception_Internal('Module `' . $moduleName . '` must exist! Existing modules: ' . join(', ', $modules));
		}
		$this->_createNamespace($moduleName, $namespace);
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
	 * @param CM_File|null $file
	 */
	private function _logFileCreation(CM_File $file = null) {
		if ($file) {
			$this->_getOutput()->writeln('Created `' . $file->getPath() . '`');
		}
	}

	/**
	 * @param array $hash
	 */
	private function _writeToComposerFile(array $hash) {
		$composerFile = new Composer\Json\JsonFile(DIR_ROOT . 'composer.json');
		$configCurrent = $composerFile->read();
		$configMerged = array_merge_recursive($configCurrent, $hash);
		$composerFile->write($configMerged);
		$this->_getOutput()->writeln('Modified `' . $composerFile->getPath() . '`');
	}

	private function _createNamespace($moduleName, $namespace) {
		/** @var \Composer\Autoload\ClassLoader $autoloader */
		$autoloader = include DIR_ROOT . 'vendor/autoload.php';
		$namespacePrefixes = $autoloader->getPrefixes();
		if (array_key_exists($namespace . '_', $namespacePrefixes)) {
			throw new CM_Cli_Exception_Internal('Namespace `' . $namespace . '` already exists');
		}
		$namespacePathRelative = $this->_getNamespaceRelativePath($moduleName, $namespace);
		$namespacePath = DIR_ROOT . $namespacePathRelative;
		CM_Util::mkDir($namespacePath);
		$this->_getOutput()->writeln('Created `' . $namespacePath . '`');

		$configAdditions = array(
				'autoload' => array(
						'psr-0' => array(
								$namespace . '_' => dirname($namespacePathRelative) . '/',
						),
				),
		);
		$this->_writeToComposerFile($configAdditions);
		$this->_dumpComposerAutoload();
	}

	/**
	 * @param string $moduleName
	 * @param string $namespace
	 * @return string
	 */
	private function _getNamespaceRelativePath($moduleName, $namespace) {
		$modulePathRelative = $this->_getModulePathRelative($moduleName);
		return $modulePathRelative . 'library/' . $namespace . '/';
	}

	public static function getPackageName() {
		return 'generator';
	}
}
