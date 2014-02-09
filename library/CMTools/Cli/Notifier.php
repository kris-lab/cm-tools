<?php

class CMTools_Cli_Notifier extends CM_Class_Abstract {

	/** @var CM_OutputStream_Stream_Output */
	private $_output;

	/**
	 * @param CM_OutputStream_Stream_Output $output
	 */
	public function __construct(CM_OutputStream_Stream_Output $output) {
		$this->_output = $output;
	}

	public function notifyCreateFile(CM_File $file) {

	}
}
