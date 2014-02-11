<?php

class CMTools_Generator_FilesystemHelper extends CM_Class_Abstract {

	/** @var CM_OutputStream_Interface */
	private $_output;

	/**
	 * @param CM_OutputStream_Interface $output
	 */
	public function __construct(CM_OutputStream_Interface $output) {
		$this->_output = $output;
	}

	/**
	 * @param string $path
	 */
	public function createDirectory($path) {
		if (is_dir($path)) {
			$this->notify('skip', $path);
		} else {
			$this->notify('mkdir', $path);
			CM_Util::mkDir($path);
		}
	}

	/**
	 * @param string $path
	 * @param string|null $content
	 * @return CM_File
	 */
	public function createFile($path, $content = null) {
		if (CM_File::exists($path)) {
			$this->notify('skip', $path);
			return new CM_File($path);
		} else {
			$this->notify('create', $path);
			return CM_File::create($path, $content);
		}

	}

	/**
	 * @param string $action
	 * @param string $path
	 */
	public function notify($action, $path) {
		$actionMessage = str_pad($action, 10, ' ', STR_PAD_LEFT);
		$this->_output->writeln($actionMessage . '  ' . $path);
	}
}
