<?php
/**
 * Php_Log install
 * @author: lovefcaaa
 */
error_reporting(E_ALL);
require_once('./file.class.php');
require_once('./mysql.class.php');

Php_Log_Logger_File::instance()->install();
Php_Log_Logger_Mysql::instance()->install();
