<?php

class CMTools_Generator_Application extends CM_Class_Abstract {

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
		$filesystemHelper = new CMTools_Generator_FilesystemHelper($this->_output);
		$filesystemHelper->createDirectory(DIR_ROOT . $path);
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

		$filesystemHelper = new CMTools_Generator_FilesystemHelper($this->_output);
		$filesystemHelper->notify('modify', $composerFile->getPath());

		$configMerged = array_merge_recursive($configCurrent, $hash);
		$composerFile->write($configMerged);

	}
}
