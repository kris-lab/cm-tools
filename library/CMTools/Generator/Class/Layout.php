<?php

class CMTools_Generator_Class_Layout extends CMTools_Generator_Class_Abstract {

	/**
	 * @param string $className
	 */
	public function createTemplateFile($className) {
		$reflectionClass = new ReflectionClass($className);
		if ($reflectionClass->isSubclassOf('CM_Form_Abstract')) {
			return;
		}
		$templatePath = $this->_getTemplateDirectory($className);
		$this->_filesystemHelper->createDirectory($templatePath);

		$content = '';
		if ($reflectionClass->isSubclassOf('CM_Page_Abstract')) {
			$content = $this->_getPageContent($reflectionClass);
		}
		$this->_createLayoutFile($className, 'default.tpl', $content);
	}

	/**
	 * @param string $className
	 */
	public function createStylesheetFile($className) {
		$reflectionClass = new ReflectionClass($className);
		if (!$reflectionClass->isSubclassOf('CM_Form_Abstract')) {
			$this->_createLayoutFile($className, 'default.less');
		}
	}

	/**
	 * @param string $className
	 * @param string $templateBasename
	 * @param string $content
	 * @throws CM_Exception_Invalid
	 * @return CM_File
	 */
	private function _createLayoutFile($className, $templateBasename, $content = null) {
		if (!$this->_classExists($className)) {
			throw new CM_Exception_Invalid('Cannot create layout for non-existing class `' . $className . '`');
		}
		$templateDirectory = $this->_getTemplateDirectory($className);
		$this->_filesystemHelper->createFile($templateDirectory . $templateBasename, $content);
	}

	/**
	 * @param string $className
	 * @return string
	 */
	private function _getTemplateDirectory($className) {
		return $this->_getClassDirectory($className) . 'layout/default/' . $this->_getTemplateDirectoryRelative($className);
	}

	/**
	 * @param string $className
	 * @return string
	 */
	private function _getTemplateDirectoryRelative($className) {
		$pathParts = explode('_', $className, 3);
		array_shift($pathParts);
		return implode('/', $pathParts) . '/';
	}

	/**
	 * @param ReflectionClass $reflection
	 * @return null|string
	 */
	private function _getPageContent(ReflectionClass $reflection) {
		if ($reflection->isSubclassOf('CM_Page_Abstract')) {
			$parentClassName = $reflection->getParentClass()->getName();
			$content = "{extends file=\$render->getLayoutPath('" . $this->_getTemplateDirectoryRelative($parentClassName) . "default.tpl'";
			if ($reflection->isAbstract()) {
				$namespace = CM_Util::getNamespace($parentClassName);
				$content .= ", '" . $namespace . "'";
			}
			$content .= ")}\n";
			return $content;
		}
		return null;
	}
}
