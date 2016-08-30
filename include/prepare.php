<?
/*
  Virtual Freer
  http://freer.ir/virtual

  Copyright (c) 2011 Mohammad Hossein Beyram, freer.ir

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v3 (http://www.gnu.org/licenses/gpl-3.0.html)
  as published by the Free Software Foundation.
*/
session_start();
ob_start();
//-------------------------------------------- Include required files
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
date_default_timezone_set('Asia/Tehran');
if($page != 'callback')
{
	require_once 'csrf-magic.php';
}
require_once 'funks.php';
require_once 'pdf.php';
require_once 'libs/class.smartmysql.php';
//-------------------------------------------- Some Vars
$now = time();
//---------------------- Conect To DB ------------------
$db = new SmartMySQL();
$db->connect($config['databaseInfo']['host'], $config['databaseInfo']['username'],  $config['databaseInfo']['password'],  $config['databaseInfo']['name']);
mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER SET utf8");
mysql_query("SET SESSION collation_connection = 'utf8_persian_ci'");
//---------------------- /Conect To DB ------------------

//---------------------- DO Clean Input vars ------------------
$request = cleaner($_REQUEST);
$dirty_request = $_REQUEST;

$post = cleaner($_POST);
$dirty_post = $_POST;

$get = cleaner($_GET);
$dirty_get = $_GET;

$get = $_GET;

$cookie = cleaner($_COOKIE);
$dirty_cookie = $_COOKIE;

$session = cleaner($_SESSION);
$dirty_session = $_SESSION;

$files = cleaner($_FILES);
$dirty_files = $_FILES;

$server = cleaner($_SERVER);
$dirty_server = $_SERVER;
//---------------------- /DO Clean Input vars ------------------
if (file_exists('../install') OR file_exists('install'))
	echo '<div style="background: #F9B8B8; color:#B70C0C; border: 1px solid #B70C0C; padding:5px; text-align:right; direction: rtl;">اخطار امنیتی: فولدر نصب حذف نشده است٬ هر چه زودتر آن را حذف کنید.</div>';
