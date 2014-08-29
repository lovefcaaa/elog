<?php
/**
 * Php_Log_Base 
 * @author: lovefcaaa
 */
abstract class Php_Log_Base {

	const LOG_LEVEL     = 'VERBOSE,ERROR,FATAL';//Line allows recording log level
	const LOG_EXCEPTION_RECORD = false;         //whether log exception information
	const LEVEL_DEBUG   = 'DEBUG';              //The debugging information
	const LEVEL_INFO    = 'INFO';               //The program output information
	const LEVEL_WARNING = 'WARNING';            //Warning log
	const LEVEL_VERBOSE = 'VERBOSE';            //Redundant log
	const LEVEL_ERROR   = 'ERROR';              //A general error
	const LEVEL_FATAL   = 'FATAL';              //A serious error caused the crash
	public $_logs       = array();              //Log information
	public $_logCount   = 0;                    //The number of log
	public $_autoFlush  = 100;                  //Auto threshold (a request log is less than 100 in memory, greater than 100 flush to the medium)
	public $_processing = false;                //Logging process switch
	public $_ip = '';                           //IP address

	abstract protected function processLogs(&$_logs);
	abstract protected function install();

	function init(){
		if(self::LOG_EXCEPTION_RECORD){
			set_error_handler(array('Php_Log_Base','appError'));
		}
	}
	
	public function flush(){
		$this->_processing = true;
		$this->processLogs($this->_logs);
		$this->_logs = array();
		$this->_logCount = 0;
		$this->_processing = false;
	}

	public function appError($errno, $errstr, $errfile, $errline){
		$errorStr = "[$errno] [".date('Ymd_His')."] $errstr ".$errfile."( $errline )";
		$this->set($errorStr, self::LEVEL_ERROR);
		
		if ($CONFIG['DEBUG']) {
			if (!is_array($error)) {
				$trace = debug_backtrace();
				$e['message']  = $error;
				$e['file'] = $trace[0]['file'];
				$e['line'] = $trace[0]['line'];
			} else {
				$e = $errorStr;
			}
			print_r($e);
		}
	}
	
	function get_client_ip(){
		if(!empty($this->_ip)){
			return $this->_ip;
		}
		$ip = '0.0.0.0';
    	if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    		$arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            if(isset($arr[0]) && !empty($arr[0])){
            	$ip = trim($arr[0]);
            }
    	}else if(isset($_SERVER['HTTP_CLIENT_IP'])) {
    		$ip = $_SERVER['HTTP_CLIENT_IP'];
    	}else if(isset($_SERVER['REMOTE_ADDR'])) {
    		$ip = $_SERVER['REMOTE_ADDR'];
    	}
    	$this->_ip = $ip;
    	return $ip;
	}

	function __destruct(){
		$this->flush();
	}
}
