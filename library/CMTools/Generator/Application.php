<?php

class CMTools_Generator_Application extends CM_Class_Abstract {

	/** @var CM_App_Installation */
	private $_installation;

	/** @var CMTools_Generator_FilesystemHelper */
	private $_filesystemHelper;

	/**
	 * @param CM_App_Installation                                  $appInstallation
	 * @param CM_OutputStream_Interface $output
	 */
	public function __construct(CM_App_Installation $appInstallation, CM_OutputStream_Interface $output) {
		$this->_installation = $appInstallation;
		$this->_filesystemHelper = new CMTools_Generator_FilesystemHelper($output);
	}

	/**
	 * @param string $name
	 * @param string $path
	 */
	public function addModule($name, $path) {
		$this->_filesystemHelper->createDirectory(DIR_ROOT . $path);
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
	 * @param string $name
	 * @param string $path
	 */
	public function addNamespace($name, $path) {
		$this->_filesystemHelper->createDirectory(DIR_ROOT . $path);
		$configAdditions = array(
				'autoload' => array(
						'psr-0' => array(
								$name . '_' => dirname($path) . '/',
						),
				),
		);
		$this->_writeToComposerFile($configAdditions);
	}

	public function dumpAutoload() {
		$composer = $this->_installation->getComposer();
		$localRepo = $composer->getRepositoryManager()->getLocalRepository();
		$package = $composer->getPackage();
		$config = $composer->getConfig();
		$im = $composer->getInstallationManager();
		$generator = $composer->getAutoloadGenerator();
		$generator->dump($config, $localRepo, $package, $im, 'composer');
	}

	/**
	 * @param array $hash
	 */
	private function _writeToComposerFile(array $hash) {
		$composerFile = new Composer\Json\JsonFile(DIR_ROOT . 'composer.json');
		$configCurrent = $composerFile->read();

		$this->_filesystemHelper->notify('modify', $composerFile->getPath());

		$configMerged = array_merge_recursive($configCurrent, $hash);
		$composerFile->write($configMerged);

	}
}
