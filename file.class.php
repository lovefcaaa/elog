<?php
/*
* PHP Enterprise-level log
*
* PHP Enterprise-level log is distributed under GPL 2
* Copyright (C) 2014 lovefcaaa <https://github.com/lovefcaaa>
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU Lesser General Public
* License as published by the Free Software Foundation; either
* version 2 of the License, or any later version.
*/

/**
 * elog_Logger_File
 * @author: lovefcaaa
 */
require_once(dirname(__FILE__).'/base.class.php');
class elog_Logger_File extends elog_Base {

	private $_maxFileSize = 40960;   //The maximum length of a file(KB)
	private $_maxLogFiles = 10;      //The number of files
	private $_logPath = './';        //The log path
	private $_logFile;
	protected static $_instance;
	
	public static function instance(){
	    if(!(self::$_instance instanceof self)) {
	        self::$_instance = new self;
	    }
	    return self::$_instance;
	}

	public function __construct(){
	    $this->init();
	    $this->setLogFile(date('Ymd').'.log');
	    
	    if($this->_logPath===false || !is_dir($this->_logPath) || !is_writable($this->_logPath)){
	        error_log('file readonly:'.$this->_logPath); 
	    }

	    if(!is_dir($this->_logPath.DIRECTORY_SEPARATOR.date('Ym'))){
	        mkdir($this->_logPath.DIRECTORY_SEPARATOR.date('Ym'));
	    }
	    $this->setLogPath($this->_logPath.DIRECTORY_SEPARATOR.date('Ym'));
	
	    if(!is_file($this->_logPath.DIRECTORY_SEPARATOR.$this->_logFile)){
	        $fp=fopen($this->_logPath.DIRECTORY_SEPARATOR.$this->_logFile,'a');
	        if(is_resource($fp)){
		        fwrite($fp,'');
		        fclose($fp);
	        }
	    }
	}
	
	protected function processLogs(&$logs){
		$logFile = $this->_logPath.DIRECTORY_SEPARATOR.$this->_logFile;
		clearstatcache();
		if(filesize($logFile) + strlen(serialize($logs))+1000 > $this->_maxFileSize*1024){
			$this->rotateFiles();		
		}
		$fwrite_string = '';
		foreach($logs as $log){
			$fwrite_string .= $this->formatLogMessage($log[0], $log[1], $log[2]);
		}
		$fp = fopen($logFile, 'a+');
        if(!empty($fp)){
            flock($fp,LOCK_EX|LOCK_NB);
            fwrite($fp,$fwrite_string);
            flock($fp,LOCK_UN);
            fclose($fp);
        }
	}
	
	public function set($message, $level = self::LEVEL_INFO){
		$level = strtoupper($level);
		if(false === strpos(self::LOG_LEVEL, $level) && $this->auth()){
				return false;
		}
		$this->_logs[] = array($message, $level, microtime(true));
		$this->_logCount++;
		if($this->_autoFlush>0 && $this->_logCount >= $this->_autoFlush && !$this->_processing){
			$this->flush();
		}
	}
	
	public function formatLogMessage($message, $level, $time){
		return "[".date('Ymd_H:i:s', $time)."] [".$this->get_client_ip()."] [$level] [$message] [".isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'NULL'."]\n";
	}
	
	public function setLogFile($param){
		$this->_logFile = $param;
	}
	
	public function setLogPath($param){
		$this->_logPath = $param;
	}

	protected function rotateFiles(){
		$file = $this->_logPath.DIRECTORY_SEPARATOR.$this->_logFile;
		$max = $this->_maxLogFiles;
		for($i = $max; $i > 0; -- $i){
			$rotateFile = $file.'_bak.'.$i.'.log';
			if(is_file($rotateFile)){
				if($i === $max){
					unlink($rotateFile);
				}else{
					rename($rotateFile,$file.'_bak.'.($i+1).'.log');
				}
			}
		}
		if(is_file($file)){
			rename($file,$file.'_bak.1'.'.log'); 
		}
	}
	
	public function auth(){
		global $CONFIG;
		if(isset($CONFIG["_PHPLOG_ProductionEnvironment"]) && !in_array(php_uname("n"), $CONFIG["_PHPLOG_ProductionEnvironment"])) {
			return false;
		}else{
			return true;
		}
	}
	
	public function install(){
		if(!is_dir($this->_logPath)){
        	return 'Error：Log storage directory does not exist ['.$this->_logPath.']';
        }
		if(!is_writable($this->_logPath)){
        	return 'Error：Log storage directory does not exist ['.$this->_logPath.']';
        }
	}
}
