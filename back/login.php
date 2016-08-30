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
if (check_login())//------------------------------------------ چک کردن لاگین بودن ادمین
{
	header('location:logout.php');
	exit;
}
	if ($post[post] == 1) 
	{
		if (!$post[username])
			$error .= "نام کاربری خود را وارد کنید.<br />";
		if (!$post[password])
			$error .= "کلمه عبور خود را وارد کنید.<br />";
		if (($post[username]) AND ($post[password])) 
		{
			$query = 'SELECT * FROM `config` LIMIT 1';
			$config = $db->fetch($query);
			if($post[username] == $config['config_admin_username'] AND md5($post[password]) == $config['config_admin_password'])
			{
				$_SESSION[admin] = 1;
				header("Location:index.php");
				exit;
			}
			else
			{
				$error .= "نام کاربری یا کلمه عبور اشتباه است.<br />";
			}
		}
	}
		include 'template/header.php';
		print_login_form($username);
		include 'template/footer.php';

//--------------------------------------------------
function print_login_form() {
	global $config,$post;
?>
	<div class="top-bar">
		<h1>ورود</h1>
		<div class="breadcrumbs"><?=$config[MainInfo][title]?>: ورود </div>
	</div><br />
	<div class="select-bar">
	</div>
<?
	include 'template/notify.php';
?>
	<form method="post" action="">
	<dt class="title"><label for="username">نام کاربری:</label></dt>
	<dt><input type="text" name="username" id="username" class="field form" value="<?=$post[username]?>" dir="ltr" size="50"></dt>
	<dt class="title"><label for="password">کلمه عبور:</label></dt>
	<dt><input type="password" name="password" id="password" class="field form" dir="ltr" size="50"></dt>
	<input type="hidden" name="post" value="1" />
	<input type="submit" name="submit" value="ورود" class="button">
	</form>
<?
}
?>