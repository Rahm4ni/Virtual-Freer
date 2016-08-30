<?
/*
  Virtual Freer
  http://freer.ir/virtual

  Copyright (c) 2011 Mohammad Hossein Beyram, freer.ir

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v3 (http://www.gnu.org/licenses/gpl-3.0.html)
  as published by the Free Software Foundation.
*/
	include '../include/configuration.php';

if (!check_login())//------------------------------------------ چک کردن لاگین بودن ادمین
{
	header('location:logout.php');
	exit;
}
	$page = 'setting';

	//-- لود کردن پلاگین ها‌
	foreach(glob("../plugins/*.php") as $plugin) {
	  require_once($plugin);
	}
	unset($plugin);

if ($request[action] == 'main')//----------------- ویرایش اطلاعات اصلی سایت
{
	if ($post[post])
	{
		$data[config_site_title]			= $post[site_title];
		$data[config_site_description]		= $post[site_description];
		$data[config_site_keyword]			= $post[site_keyword];
		$data[config_operator_description]	= $post[operator_description];
		$data[config_description]			= $post[description];
		$data[config_admin_email]			= $post[admin_email];
		$data[config_admin_yahoo_id]		= $post[admin_yahoo_id];
		$data[config_admin_username]		= $post[admin_username];
		$data[config_input_validate]		= $post[input_validate];
		
		if (!$post[site_title])
			$error .= 'عنوان سایت را وارد کنید.<br />';
		if (!$post[admin_email])
			$error .= 'ایمیل را وارد کنید.<br />';
		elseif (filter_var($post[admin_email], FILTER_VALIDATE_EMAIL)== false)
		{
			$error .= 'ایمیل وارد شده نامعتبر است.<br />';
		}
		if (!$post[admin_username])
			$error .= 'نام کاربری مدیر را وارد کنید.<br />';
		
		if ($post[admin_password] OR $post[password] OR $post[confirm_password])
		{
			if (!$post[admin_password])
				$error .= 'کلمه عبور فعلی را وارد کنید.<br />';
			else
			{
				$sql 	= 'SELECT * FROM `config` WHERE `config_id` = "1" AND `config_admin_password` = MD5("'.$post[admin_password].'") LIMIT 1;';
				$pass 	= $db->fetch($sql);
				if ($pass[config_admin_password] != md5($post[admin_password]))
					$error .= 'کلمه عبور فعلی اشتباه است.<br />';
			}
			if (!$post[password])
				$error .= 'کلمه عبور جدید را وارد کنید.<br />';
			if (!$post[confirm_password])
				$error .= 'تکرار کلمه عبور جدید را وارد کنید.<br />';
			if ($post[password] AND $post[confirm_password] AND $post[password] != $post[confirm_password])
				$error .= 'کلمه عبور جدید با تکرار آن یکی نیست.<br />';
			if (!$error)
				$data[config_admin_password]	= md5($post[password]);
		}

		if (!$error)
		{
			//-------------------------------------- آپدیت اطلاعات
			$sql 		= $db->queryUpdate('config', $data, 'WHERE `config_id` = "1" LIMIT 1');
			$db->execute($sql);
			header("location:?action=main&edit=1");
			exit;
		}
	}
	else
	{
		$sql = 'SELECT * FROM `config` WHERE `config_id` = "1" LIMIT 1;';
		$data = $db->fetch($sql);
	}
	include 'template/header.php';
	config_form($data);
	include 'template/footer.php';
}
elseif ($request[plugin] AND is_installed($request[plugin]))//----------------------- ویرایش اطلاعات پلاگین ها
{
	if($post[post])
	{
		foreach ($pluginData[$request[plugin]][field][config] as $field)
		{
			$sql = 'SELECT * FROM `plugindata` WHERE `plugindata_uniq` = "'.$pluginData[$request[plugin]][uniq].'" AND `plugindata_field_name` = "'.$field[name].'";';
			$data = $db->fetch($sql);
			if (isset($data[plugindata_field_value]))
			{
				$update[plugindata_field_value] = $post[$field[name]];
				$sql = $db->queryUpdate('plugindata', $update, 'WHERE `plugindata_uniq` = "'.$pluginData[$request[plugin]][uniq].'" AND `plugindata_field_name` = "'.$field[name].'" LIMIT 1;');
				$db->execute($sql);
			}
			else
			{
				$insert[plugindata_uniq] 		= $pluginData[$request[plugin]][uniq];
				$insert[plugindata_field_name] 	= $field[name];
				$insert[plugindata_field_value] = $post[$field[name]];
				$sql = $db->queryInsert('plugindata', $insert);
				$db->execute($sql);
			}
		}
		header('location:?plugin='.$request[plugin].'&edit=1');
		exit;
	}
	include 'template/header.php';
	plugin_form($pluginData[$request[plugin]]);
	include 'template/footer.php';
}
else
{
	header('location:?action=main');
	exit;
}
//------------------------------------------------------------- Local Functions
function is_installed($uniq)
{
	global $db;
	$result = $db->retrieve('plugin_id','plugin','plugin_uniq',$uniq);
	if($result)
	{
		return true;
	}
	else
	{
		return false;
	}
}
//------------------------------- چاپ فرم اطلاعات اصلی سایت
function config_form ($data) {
	global $config,$post;
?>
	<div class="top-bar">
		<h1>تنظیمات</h1>
		<div class="breadcrumbs"><a href="index.php">خانه</a> / <a href="setting.php">تنظیمات</a> / تنظیمات اصلی</div>
	</div><br />
	<div class="select-bar">
	</div>
<?
	include 'template/notify.php';
?>
<form method="post" action="">
	<dt class="title"><label for="site_title">عنوان سایت:</label></dt>
	<dt><input type="text" name="site_title" id="site_title" class="field form" value="<?=$data[config_site_title]?>" size="60"></dt>
	<dt class="title"><label for="site_description">توضیح سایت:</label></dt>
	<dt><input type="text" name="site_description" id="site_description" class="field form" value="<?=$data[config_site_description]?>" size="60"></dt>
	<dt class="title"><label for="site_keyword">کلمات کلیدی سایت:</label></dt>
	<dt><textarea class="field form" cols="57" rows="3" id="site_keyword" name="site_keyword"><?=$data[config_site_keyword]?></textarea></dt>
	<dt class="title"><label for="operator_description">توضیحات باکس انتخاب اپراتور:</label></dt>
	<dt><textarea class="field form" cols="57" rows="3" id="operator_description" name="operator_description"><?=$data[config_operator_description]?></textarea></dt>
	<dt class="title"><label for="description">توضیحات صفحه خرید:</label></dt>
	<dt><textarea class="field form" cols="57" rows="3" id="description" name="description"><?=$data[config_description]?></textarea></dt>
	<dt class="title"><label for="admin_email">ایمیل مدیر:</label></dt>
	<dt><input type="text" name="admin_email" id="admin_email" class="field form" dir="ltr" value="<?=$data[config_admin_email]?>" size="60"></dt>
	<dt class="title"><label for="admin_yahoo_id">آی‌دی یاهو مدیر (جهت پشتیبانی):</label></dt>
	<dt><input type="text" name="admin_yahoo_id" id="admin_yahoo_id" class="field form" dir="ltr" value="<?=$data[config_admin_yahoo_id]?>" size="60"></dt>
	<dt class="title"><label for="admin_username">نام‌کاربری‌ مدیر:</label></dt>
	<dt><input type="text" name="admin_username" id="admin_username" class="field form" dir="ltr" value="<?=$data[config_admin_username]?>" size="60"></dt>
	<dt class="title"><label for="admin_password">کلمه عبور فعلی:</label></dt>
	<dt><input type="password" name="admin_password" id="admin_password" class="field form" dir="ltr" value="" size="60" autocomplete="off"></dt>
	<dt class="title"><label for="password">کلمه عبور جدید</label></dt>
	<dt><input type="password" name="password" id="password" class="field form" dir="ltr" value="" size="60"></dt>
	<dt class="title"><label for="confirm_password">تکرار کلمه عبور جدید:</label></dt>
	<dt><input type="password" name="confirm_password" id="confirm_password" class="field form" dir="ltr" value="" size="60"></dt>
	<dt><input type="checkbox" name="input_validate" value="1"<? if ($data[config_input_validate] == 1) echo ' checked'; ?> />اعتبارسنجی شماره تماس و ایمیل خریدار</dt>
	<input type="hidden" name="post" value="1" />
	<input type="submit" name="submit" value="ذخیره" class="button">
</form>
<?
}

//-------------------------------- چاپ فرم پلاگین ها
function plugin_form ($plugin)
{
	global $config,$db;
?>
	<div class="top-bar">
		<h1>تنظیمات</h1>
		<div class="breadcrumbs"><a href="index.php">خانه</a> / <a href="setting.php">تنظیمات</a> / <?=$plugin[name]?></div>
	</div><br />
	<div class="select-bar">
	</div>
<?
	include 'template/notify.php';
?>
	<form method="post" action="">
<? 
	echo $plugin[note];
	foreach ($plugin[field][config] as $field)
	{
		$sql = 'SELECT * FROM `plugindata` WHERE `plugindata_uniq` = "'.$plugin[uniq].'" AND `plugindata_field_name` = "'.$field[name].'";';
		$data = $db->fetch($sql);
?>
		<dt class="title"><label for="<?=$field[name]?>"><?=$field[title]?>:</label></dt>
		<dt><input type="text" name="<?=$field[name]?>" id="<?=$field[name]?>" class="field form" value="<?=$data[plugindata_field_value]?>" size="60"></dt>
<?
	}
?>
	<input type="hidden" name="post" value="1" />
	<input type="submit" name="submit" value="ذخیره" class="button">
</form>
<?
}
