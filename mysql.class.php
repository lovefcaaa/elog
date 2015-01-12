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
 * elog_Logger_Mysql
 * @author: lovefcaaa
 */
require_once(dirname(__FILE__).'/base.class.php');
class elog_Logger_Mysql extends elog_Base {
    
    public $_tablename = "logs";
    public static $type = array(
                    0 => 'The default ',
                    1 => 'Business A ',
                    2 => 'Business B',
                    3 => 'Business C',
    );
    protected static $_instance;
    private $_db;
    private $_field = array('user_id', 'user_name', 'message', 'gmt_create', 
    			    'ip', 'reason', 'type', 'object_id', 'operater', );
    
    public static function instance() {
        if(!(self::$_instance instanceof self)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    public function __construct() {
        $this->init();
        $this->initDB();
    }
    
    protected function processLogs(&$logs) {
        if(empty($logs)){
            return ;
        }
        $insert_sql = 'INSERT INTO `'.$this->_tablename.'` (`'.implode("`,`", $this->_field).'`) VALUES ';
        foreach($logs as $log){
            $insert_sql .= "('".implode("','", $log)."'),";
        }
        $insert_sql = substr($insert_sql, 0, -1).';';
        try{
            return $this->_db->exec($insert_sql);
        }catch(Exception $e){
            error_log($this->_tablename." error:".$e->getMessage());
        }
        elog_Logger_File::instance()->set('PHP_MYSQL_LOG_ERROR:'.$insert_sql, 'error');
        return false;
    }
    
    public function set($message, $operater = '1', $type = '0', $object_id = '0', 
    			$reason = '', $user_id = 0, $user_name = '') {
        $this->_logs[$this->_logCount]['user_id'] = intval($user_id);
        $this->_logs[$this->_logCount]['user_name'] = $user_name;
        $this->_logs[$this->_logCount]['message'] = 
        	addslashes( empty($message{255}) ? $message : substr($message,0,255) );
        $this->_logs[$this->_logCount]['gmt_create'] = date('Y-m-d H:i:s', time());
        $this->_logs[$this->_logCount]['ip'] = $this->get_client_ip();
        $this->_logs[$this->_logCount]['reason'] = $reason;
        $this->_logs[$this->_logCount]['type'] = intval($type);
        $this->_logs[$this->_logCount]['object_id'] = intval($object_id);
        $this->_logs[$this->_logCount]['operater'] = intval($operater);
        $this->_logCount++;
        if($this->_autoFlush>0 && $this->_logCount >= $this->_autoFlush && !$this->_processing){
            $this->flush();
        }
    }

    public function initDB(){
    	try{
            $this->_db = new pdo('mysql:host=127.0.0.1;port=3306;dbname=root;charset=gbk',
                        'root',
                        'rootpass');
        }catch(Exception $e){
            error_log(__CLASS__."ERROR：".$e->getMessage());
        }
	}
	
	public function install(){
		$exists = $this->_db->query($sql)->fetchAll();
		if(empty($exists)){
			$sql = "CREATE TABLE `logs` (
                  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Log ID',
                  `user_id` bigint(20) DEFAULT NULL COMMENT 'The user ID ',
                  `user_name` varchar(32) DEFAULT NULL COMMENT 'The user name ',
                  `operater` tinyint(4) DEFAULT '1' COMMENT 'Mode of operation(1-increase-2-delete-3-Modify-4-query)',
                  `object_id` bigint(20) DEFAULT '0' COMMENT 'The action object ID ',
                  `type` tinyint(4) DEFAULT '0' COMMENT 'Log type ',
                  `reason` varchar(256) DEFAULT NULL COMMENT 'Log notes ',
                  `ip` varchar(16) DEFAULT '127.0.0.1' COMMENT 'IP',
                  `message` varchar(512) DEFAULT NULL COMMENT 'Log information ',
                  `gmt_create` datetime DEFAULT NULL COMMENT 'Log time ',
                  PRIMARY KEY (`id`),
                  KEY `loging` (`operater`,`type`)
                ) ENGINE=InnoDB DEFAULT CHARSET=gbk COMMENT='Business log ';";
            $flag = $this->_db->exec($sql);
            if($flag){
            	return 'Status:Log Mysql table to create success';
            }else{
            	return 'Error:Log Mysql table creation failed';
            }
		}else{
			return 'Error:Mysql table already exists';
		}
        
    }
}
