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

<?php
/**
 * Php_Log install
 * @author: lovefcaaa
 */
error_reporting(E_ALL);
require_once('./file.class.php');
require_once('./mysql.class.php');

elog_Logger_File::instance()->install();
elog_Logger_Mysql::instance()->install();
