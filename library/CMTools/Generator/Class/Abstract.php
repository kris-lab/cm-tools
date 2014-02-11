<?php

abstract class CMTools_Generator_Class_Abstract {

	/** @var CM_App_Installation */
	protected $_appInstallation;

	/** @var CMTools_Generator_FilesystemHelper */
	protected $_filesystemHelper;

	/**
	 * @param CM_App_Installation       $appInstallation
	 * @param CM_OutputStream_Interface $output
	 */
	public function __construct(CM_App_Installation $appInstallation, CM_OutputStream_Interface $output) {
		$this->_appInstallation = $appInstallation;
		$this->_filesystemHelper = new CMTools_Generator_FilesystemHelper($output);
	}

	/**
	 * @param string $className
	 * @return string
	 * @throws CM_Exception_Invalid
	 */
	protected function _getParentClassName($className) {
		$parts = explode('_', $className);
		$classNamespace = array_shift($parts);
		$type = array_shift($parts);
		$namespaces = array_reverse(CM_Bootloader::getInstance()->getNamespaces());
		$position = array_search($classNamespace, $namespaces);
		if (false === $position) {
			throw new CM_Exception_Invalid('Namespace `' . $classNamespace . '` not found within `' . implode(', ', $namespaces) . '` namespaces.');
		}
		$namespaces = array_splice($namespaces, $position);
		foreach ($namespaces as $namespace) {
			$className = $namespace . '_' . $type . '_Abstract';
			if ($this->_classExists($className)) {
				return $className;
			}
		}
		return 'CM_Class_Abstract';
	}

	/**
	 * @param string $className
	 * @return string
	 * @throws CM_Exception_Invalid
	 */
	protected function _getClassDirectory($className) {
		$namespace = CM_Util::getNamespace($className);
		$namespaces = CM_Bootloader::getInstance()->getNamespaces();
		if (!in_array($namespace, $namespaces)) {
			throw new CM_Exception_Invalid('Cannot find `' . $namespace . '` namespace');
		}
		return CM_Bootloader::getInstance()->getNamespacePath($namespace);
	}

	/**
	 * @param string $className
	 * @return bool
	 */
	protected function _classExists($className) {
		$namespace = CM_Util::getNamespace($className);
		$classPath = CM_Util::getNamespacePath($namespace) . 'library/' . str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
		return CM_File::exists($classPath);
	}
}
