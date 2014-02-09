<?php

class CMTools_Generator_Builder extends CM_Class_Abstract {

	/** @var CM_App_Installation */
	private $_installation;

	/** @var CM_OutputStream_Abstract */
	private $_output;

	/**
	 * @param CM_App_Installation      $appInstallation
	 * @param CM_OutputStream_Abstract $output
	 */
	public function __construct(CM_App_Installation $appInstallation, CM_OutputStream_Abstract $output) {
		$this->_installation = $appInstallation;
		$this->_output = $output;
	}

	/**
	 * @param string $name
	 * @param string $path
	 */
	public function addModule($name, $path) {
		$this->_createDirectory(DIR_ROOT . $path);
		$configAdditions = array(
				'extra' => array(
						'cm-modules' => array(
								$name => array(
										'path' => $path,
								),
						),
				),
		);
		$this->_writeToComposerFile($configAdditions);
	}

	/**
	 * @param array $hash
	 */
	private function _writeToComposerFile(array $hash) {
		$composerFile = new Composer\Json\JsonFile(DIR_ROOT . 'composer.json');
		$configCurrent = $composerFile->read();
		$this->_notify('modify', $composerFile->getPath());
		$configMerged = array_merge_recursive($configCurrent, $hash);
		$composerFile->write($configMerged);
	}

	/**
	 * @param string $path
	 */
	private function _createDirectory($path) {
		if (is_dir($path)) {
			$this->_notify('skip', $path);
		} else {
			$this->_notify('create', $path);
		}
		CM_Util::mkDir($path);
	}

	/**
	 * @param string $action
	 * @param string $path
	 */
	private function _notify($action, $path) {
		$actionMessage = str_pad($action, 7, ' ', STR_PAD_LEFT);
		$this->_output->writeln($actionMessage . ' ' . $path);
	}
}
