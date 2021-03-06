<?php

class CMTools_AppInstallation extends CM_App_Installation {

	/**
	 * @param string $name
	 * @return bool
	 */
	public function moduleExists($name) {
		return array_key_exists($name, $this->getModulePaths());
	}

	/**
	 * @return mixed
	 * @throws CM_Exception_Invalid
	 */
	public function getModulesPath() {
		$modulesPaths = $this->getModulesPathsAvailable();
		if (count($modulesPaths) > 1) {
			throw new CM_Exception_Invalid('Multiple module root paths in project.');
		}
		if (count($modulesPaths) === 0) {
			return 'modules/';
		}
		return reset($modulesPaths);
	}

	/**
	 * @return CM_App_Module[]
	 */
	public function getModules() {
		return $this->_getRootPackage()->getModules();
	}

	/**
	 * @return string[]
	 */
	public function getModulesPathsAvailable() {
		$modulePaths = array();
		foreach ($this->getModules() as $module) {
			$modulePaths[] = $module->getPath();
		}
		$modulePaths = array_map('dirname', $modulePaths);
		return array_unique($modulePaths);
	}

	/**
	 * @return bool
	 */
	public function isSingleModuleStructure() {
		return count($this->getModulesPathsAvailable()) === 1 && $this->getModulesPath() === '';
	}

	public function addModule(CM_App_Module $module) {

	}

	/**
	 * @return CM_App_Package
	 */
	private function _getRootPackage() {
		$rootPackageName = $this->getComposer()->getPackage()->getName();
		foreach ($this->getPackages() as $package) {
			if ($package->getName() === $rootPackageName) {
				return $package;
			}
		}
	}
}
