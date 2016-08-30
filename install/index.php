<?
/*
  Virtual Freer
  http://freer.ir/virtual

  Copyright (c) 2011 Mohammad Hossein Beyram, freer.ir

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v3 (http://www.gnu.org/licenses/gpl-3.0.html)
  as published by the Free Software Foundation.
*/
error_reporting(E_ALL & ~E_NOTICE);

if ($_POST[post])
{
	//-- چک کردن ورودي‌ها
	if (!$_POST[site_url])
		$error .= '<li>آدرس سايت را وارد کنيد.</li>';
	elseif (filter_var($_POST[site_url], FILTER_VALIDATE_URL)== false)
		$error .= '<li>آدرس سايت معتبر نيست.</li>';
	if (!$_POST[site_path])
		$error .= '<li>مسير نصب را وارد کنيد.</li>';
	if (!$_POST[db_host])
		$error .= '<li>نام ميزبان پايگاه داده را وارد کنيد.</li>';
	if (!$_POST[db_name])
		$error .= '<li>نام پايگاه داده را وارد کنيد.</li>';
	if (!$_POST[db_username])
		$error .= '<li>نام کاربري پايگاه داده را وارد کنيد.</li>';
	if ($_POST[admin_email] AND filter_var($_POST[admin_email], FILTER_VALIDATE_EMAIL)== false)
		$error .= '<li>ايميل مدير معتبر نيست.</li>';
	if (!$_POST[admin_username])
		$error .= '<li>نام کاربري مدير را وارد کنيد.</li>';
	if (!$_POST[password])
		$error .= '<li>کلمه عبور مدير را وارد کنيد.</li>';
	if (!$_POST[confirm_password])
		$error .= '<li>تکرار کلمه عبور مدير را وارد کنيد.</li>';
	if ($_POST[password] AND $_POST[confirm_password] AND $_POST[password]!=$_POST[confirm_password])
		$error .= '<li>کلمه عبور و تکرار آن يکي نيست.</li>';
	if (!$error)
	{
		//-- چک کردن کانکشن ديتابيس
		require_once '../include/libs/class.smartmysql.php';
		$db = new SmartMySQL();
		$con = $db->connect($_POST[db_host], $_POST[db_username],  $_POST[db_password],  $_POST[db_name]);
		if (!$con)
			$error .= '<li>اطلاعات اتصال به پايگاه داده درست نيست.</li>';
		if(!$error)
		{
			mysql_query("SET NAMES 'utf8'");
			mysql_query("SET CHARACTER SET utf8");
			mysql_query("SET SESSION collation_connection = 'utf8_persian_ci'");
			//-- ساخت فايل تنظيمات? ساخت ديتابيس و وارد کردن اطلاعات تنظيمات
			write_config_file($_POST);
			file2db('virtual.sql');
			$config[config_site_title]			= $_POST[site_title];
			$config[config_site_description]	= $_POST[site_description];
			$config[config_site_keyword]		= $_POST[site_keyword];
			$config[config_admin_email]			= $_POST[admin_email];
			$config[config_admin_username]		= $_POST[admin_username];
			$config[config_admin_password]		= md5(trim($_POST[password]));
			$sql = $db->queryInsert('config', $config);
			$db->execute($sql);
			success($_POST);
			exit;
		}
	}
}
else
{
	//-- ست کردن مقادير پيشفرض
	$_POST[site_url] = dirname('http://'.$_SERVER[SERVER_NAME].$_SERVER['REQUEST_URI']).'/';
	$_POST[site_path] = dirname(dirname(__FILE__)).'/';
	$_POST[db_host] = 'localhost';
	$_POST[site_title] = 'فروش اينترنتي شارژ';
	$_POST[site_keyword] = 'فروش , شارژ , اينترنتي , آنلاين , شتاب , ايرانسل , همراه اول , تاليا';
	$_POST[db_key] = genRandomString(12);
}
form($_POST,$error);

//---------------------------------------------------------- 
function form($data,$error)
{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Virtual Freer</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<style>
	body {
		font-family: tahoma;
		font-size: 12px;
		text-align: right;
		direction: rtl;
	}
	#content {
	  width: 500px ;
	  background: #F5F5F5;
	  border: 1px dashed #888585;
	  padding: 15px;
	  margin: 30px 0;
	  margin-left: auto ;
	  margin-right: auto ;
	}
	#right-col{
		width: 47%;
		float: right;
		padding: 10px;
	}
	
	#left-col{
		width: 47%;
		float: left;
		padding: 10px;
	}
	dt.section {
		background:#F5F5F5;
		color:#000000;
		border: 1px solid #898585;
		font-size: 20px;
		font-family: arial;
		padding:5px 15px;
		margin:5px;
		width:450px;
	}
	dt.title {
		font-weight: bold;
		padding:5px;
	}
	dt{
		padding:5px;
	}
	.form {
		padding: 3px;
		font-family: tahoma;
		font-size: 10px;
		border: 1px solid;
	}
	.error {
		color: #ED1C24;
	}
	input[type=submit]
	{
		border: 1px solid #000;
		
	}
</style>
</head>
<body>
<div id="content">
<?
	if($error)	echo '<div class="error"><dt class="section">خطا</dt><ul>'.$error.'</ul></div>';
?>
<form method="post" action="">
	<dt class="section">اطلاعات اصلي</dt>
	<dt class="title"><label for="site_url">آدرس سايت:</label></dt>
	<dt><input type="text" name="site_url" id="site_url" class="form" dir="ltr" value="<?=$data[site_url]?>" size="60"><br /><small>حتما همراه با "/" در انتها</small></dt>
	<dt class="title"><label for="site_path">آدرس فيزيکي محل برنامه:</label></dt>
	<dt><input type="text" name="site_path" id="site_path" class="form" dir="ltr" value="<?=$data[site_path]?>" size="60"><br /><small>حتما همراه با "/" در انتها</small></dt>
	
	<dt class="section">اطلاعات پايگاه داده</dt>
	<dt class="title"><label for="db_host">ميزبان:</label></dt>
	<dt><input type="text" name="db_host" id="db_host" class="form" dir="ltr" value="<?=$data[db_host]?>" size="60"></dt>
	<dt class="title"><label for="db_name">نام:</label></dt>
	<dt><input type="text" name="db_name" id="db_name" class="form" dir="ltr" value="<?=$data[db_name]?>" size="60"></dt>
	<dt class="title"><label for="db_username">نام کاربري:</label></dt>
	<dt><input type="text" name="db_username" id="db_username" class="form" dir="ltr" value="<?=$data[db_username]?>" size="60"></dt>
	<dt class="title"><label for="db_password">کلمه عبور:</label></dt>
	<dt><input type="password" name="db_password" id="db_password" class="form" dir="ltr" value="" size="60"></dt>
	<dt class="title"><label for="db_key">کليد رمزنگاري اطلاعات:</label></dt>
	<dt><input type="text" name="db_key" id="db_key" class="form" dir="ltr" value="<?=$data[db_key]?>" size="60"></dt>
	
	<dt class="section">تنظيمات سايت</dt>
	<dt class="title"><label for="site_title">عنوان سايت:</label></dt>
	<dt><input type="text" name="site_title" id="site_title" class="form" value="<?=$data[site_title]?>" size="60"></dt>
	<dt class="title"><label for="site_description">توضيح سايت:</label></dt>
	<dt><input type="text" name="site_description" id="site_description" class="form" value="<?=$data[site_description]?>" size="60"></dt>
	<dt class="title"><label for="site_keyword">کلمات کليدي سايت:</label></dt>
	<dt><textarea class="form" cols="57" rows="3" id="site_keyword" name="site_keyword"><?=$data[site_keyword]?></textarea></dt>
	<dt class="title"><label for="admin_email">ايميل مدير:</label></dt>
	<dt><input type="text" name="admin_email" id="admin_email" class="form" dir="ltr" value="<?=$data[admin_email]?>" size="60"></dt>
	<dt class="title"><label for="admin_username">نام‌کاربري‌ مدير:</label></dt>
	<dt><input type="text" name="admin_username" id="admin_username" class="field form" dir="ltr" value="<?=$data[admin_username]?>" size="60"></dt>
	<dt class="title"><label for="password">کلمه عبور:</label></dt>
	<dt><input type="password" name="password" id="password" class="field form" dir="ltr" value="" size="60"></dt>
	<dt class="title"><label for="confirm_password">تکرار کلمه عبور:</label></dt>
	<dt><input type="password" name="confirm_password" id="confirm_password" class="field form" dir="ltr" value="" size="60"></dt>
	<dt><input type="hidden" name="post" value="1" />
	<input type="submit" name="submit" value="نصب" class="form"></dt>
</form>
</div>

</body>
</html>
<?
}

//---------------------------------------------------------- 
function success($data)
{
	?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Virtual Freer</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<style type="text/css">
	body {
		font-family: tahoma;
		font-size: 12px;
		text-align: right;
		direction: rtl;
	}
	#content {
	  width: 500px ;
	  background: #F5F5F5;
	  border: 1px dashed #888585;
	  padding: 15px;
	  margin: 30px 0;
	  margin-left: auto ;
	  margin-right: auto ;
	}
	#content a {
		text-decoration: none;
		font-weight: bold;
	}
</style>
</head>
<body>
	<div id="content">
		<b>Virtual Freer با موفقيت نصب شد.</b><br />
		<p>
			نام کاربري مدير: <?=$data[admin_username]?><br />
			کلمه عبور مدير: <?=$data[password]?><br />
		</p>
		<p>
			کليد رمزنگاري اطلاعات پايگاه داده را در جايي مطمئن نگه‌داريد? بدون داشتن اين کليد رمزگشايي از اطلاعات کارت‌ها غيرممکن خواهد بود<br />
			کليد: <?=$data[db_key]?><br />
		</p>
		<p><font color="red">حتما قبل از ورود به پنل مديريت فولدر install را حذف کنيد با وجود اين فولدر امنيت سايت شما به خطر خواهد افتاد.</font></p>
		<a href="../back">پنل مديريت</a>
	</div>
</body>
</html>
<?
}

//---------------------------------------------------------- 
function write_config_file($data)
{
	$configFile = "../include/configuration.php";
	$fh = fopen($configFile, 'w') or die("can't open file");
	$stringData = "<?php
//-------------------------------------------- Config Array
\$config=array(
	'MainInfo'=>array(
		'url'		=> '".$data[site_url]."' , //-- با اسلش آخر
		'path'		=> '".$data[site_path]."' , //-- با اسلش آخر
		'upload'	=> array(
					'image'			=> 'statics/upload/images/' , //-- با اسلش آخر
					'attachment'	=> 'statics/upload/attachments/' , //-- با اسلش آخر
		)
	),
	'databaseInfo'=>array(
		'host'=>'".$data[db_host]."',
		'name'=>'".$data[db_name]."',
		'username'=>'".$data[db_username]."',
		'password'=>'".$data[db_password]."',
		'salt'=>'".$data[db_key]."',
		'collation'=>'utf8',
	),
	'category'=>array(
		'includedScripts' => 'colorbox',
		'enrtyPerPage' => '20',
	),
	'card'=>array(
		'includedScripts' => '',
		'enrtyPerPage' => '20',
		'reserveExpire' => '20', //-- مدت زمان رزرو کارت به دقيقه
	),
	'report'=>array(
		'includedScripts' => 'datepicker',
		'enrtyPerPage' => '20',
	),
	'product'=>array(
		'includedScripts' => '',
		'enrtyPerPage' => '20',
	),
	'payment'=>array(
		'enrtyPerPage' => '20',
	),
);
//---------------------- Prepare ------------------
require_once 'prepare.php';";
	fwrite($fh, $stringData);
	fclose($fh);
}

//---------------------------------------------------------- 
function file2db($sql_file)
{
	global $db;
	if (file_exists($sql_file))
	{
		$fd = fopen($sql_file, 'rb');
		$import_queries = fread($fd, filesize($sql_file));
		fclose($fd);
	}
	else
	{
		return false;
	}
    if (!get_cfg_var('safe_mode'))
		@set_time_limit(0);
    $sql_queries = array();
    $sql_length = strlen($import_queries);
    $pos = strpos($import_queries, ';');
    for ($i=$pos; $i<$sql_length; $i++)
    {
		// remove comments
		if ($import_queries[0] == '#') {
			$import_queries = ltrim(substr($import_queries, strpos($import_queries, "\n")));
			$sql_length = strlen($import_queries);
			$i = strpos($import_queries, ';')-1;
			continue;
		}
		if ($import_queries[($i+1)] == "\n")
		{
			$next = '';
			for ($j=($i+2); $j<$sql_length; $j++)
			{
				if (!empty($import_queries[$j]))
				{
	                $next = substr($import_queries, $j, 6);
	                if ($next[0] == '#')
	                {
					// find out where the break position is so we can remove this line (#comment line)
	                	for ($k=$j; $k<$sql_length; $k++)
	                	{
	                		if ($import_queries[$k] == "\n")
	                		{
	                      		break;
	                    	}
	                  	}
	                	$query = substr($import_queries, 0, $i+1);
	                	$import_queries = substr($import_queries, $k);
	                	// join the query before the comment appeared, with the rest of the dump
	                	$import_queries = $query . $import_queries;
	                	$sql_length = strlen($import_queries);
	                	$i = strpos($import_queries, ';')-1;
	                	continue 2;
	                }
					break;
				}
			}
			if (empty($next))
			{ 
				// get the last insert query
				$next = 'insert';
			}
			if ((strtoupper($next) == 'DROP T') || (strtoupper($next) == 'CREATE') || (strtoupper($next) == 'INSERT'))
			{
				$next = '';
				$sql_query = substr($import_queries, 0, $i);
				/*if ($table_prefix !== -1)
				{
					if (strtoupper(substr($sql_query, 0, 25)) == 'DROP TABLE IF EXISTS TOC_')
					{
	                  $sql_query = 'DROP TABLE IF EXISTS ' . $table_prefix . substr($sql_query, 25);
	                }
	                elseif (strtoupper(substr($sql_query, 0, 17)) == 'CREATE TABLE TOC_')
	                {
	                  $sql_query = 'CREATE TABLE ' . $table_prefix . substr($sql_query, 17);
	                }
	                elseif (strtoupper(substr($sql_query, 0, 16)) == 'INSERT INTO TOC_')
	                {
	                  $sql_query = 'INSERT INTO ' . $table_prefix . substr($sql_query, 16);
	                }
				}*/
				$sql_queries[] = trim($sql_query);
				$import_queries = ltrim(substr($import_queries, $i+1));
				$sql_length = strlen($import_queries);
				$i = strpos($import_queries, ';')-1;
			}
		}
	}
    for ($i=0, $n=sizeof($sql_queries); $i<$n; $i++)
    {
    	$db->execute($sql_queries[$i]);
    }
}

//---------------------------------------------------------- 
function genRandomString($length=10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $string = '';
    for ($p = 0; $p < $length; $p++) {
        $string .= $characters[mt_rand(0, strlen($characters))];
    }
    return $string;
}
