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
	$page = 'plugins';

	//-- لود کردن پلاگین ها
	foreach(glob("../plugins/*.php") as $plugin) {  
	  require_once($plugin);  
	}
	unset($plugin);

if ($request[action] == 'change' AND $request[uniq])
{
	$status = $db->retrieve('plugin_status','plugin','plugin_uniq',$request[uniq]);
	if (!$status)
	{
		$plugin[plugin_uniq]	= $request[uniq];
		$plugin[plugin_name]	= $pluginData[$request[uniq]][name];
		$plugin[plugin_type]	= $pluginData[$request[uniq]][type];
		$plugin[plugin_status]	= 1;
		$plugin[plugin_time]	= $now;
		$sql = $db->queryInsert('plugin', $plugin);
		$db->execute($sql);
	}
	elseif ($status == 2)
	{
		$update[plugin_status]	= 1;
		$update[plugin_name]	= $pluginData[$request[uniq]][name];
		$update[plugin_type]	= $pluginData[$request[uniq]][type];
		$sql = $db->queryUpdate('plugin', $update, 'WHERE `plugin_uniq` = "'.$request[uniq].'" LIMIT 1;');
		$db->execute($sql);
	}
	elseif ($status == 1)
	{
		$update[plugin_status]	= 2;
		$update[plugin_name]	= $pluginData[$request[uniq]][name];
		$update[plugin_type]	= $pluginData[$request[uniq]][type];
		$sql = $db->queryUpdate('plugin', $update, 'WHERE `plugin_uniq` = "'.$request[uniq].'" LIMIT 1;');
		$db->execute($sql);
	}
	header('location:?action=list&save=1');
	exit;
}
elseif ($request[action] == 'list')//---------------------------------- لیست پلاگین‌ها‌
{
	include 'template/header.php';
	plugins ($pluginData);
	include 'template/footer.php';
	exit;

}
else
{
	header('location:?action=list');
	exit;
}


//------------------------------------------------------------- Local Functions
//------------------------------- چاپ فرم امار کارت‌ها
function plugins ($pluginData)
{
	global $config,$db;
?>
	<div class="top-bar">
		<h1>پلاگین‌ها</h1>
		<div class="breadcrumbs"><a href="index.php">خانه</a> / پلاگین‌ها</div>
	</div><br />
	<div class="select-bar">
	</div>
<?	include 'template/notify.php';	?>
	<div class="table">
		<img src="../statics/image/bg-th-left.gif" width="8" height="7" alt="" class="left" />
		<img src="../statics/image/bg-th-right.gif" width="7" height="7" alt="" class="right" />
		<table class="listing" cellpadding="0" cellspacing="0">
			<tr>
				<th class="first">نام</th>
				<th width="70" align="center">تنظیمات</th>
				<th width="70" align="center">ناشر</th>
				<th width="90" align="center">وضعیت</th>
			</tr>
<?
	foreach($pluginData as $plugin)
	{
		$status = $db->retrieve('plugin_status','plugin','plugin_uniq',$plugin[uniq]);
		if ($status == 1)
		{
			$status = '<font color="green">فعال</font> (<a href="?action=change&uniq='.$plugin[uniq].'">غیرفعال</a>)';
			$config = '<a href="setting.php?plugin='.$plugin[uniq].'">تنظیمات</a>';
		}
		elseif ($status == 2)
		{
			$status = '<font color="red">غیرفعال</font> (<a href="?action=change&uniq='.$plugin[uniq].'">فعال</a>)';
			$config = '<a href="setting.php?plugin='.$plugin[uniq].'">تنظیمات</a>';
		}
		else
		{
			$status = '<font color="red">نصب نشده</font> (<a href="?action=change&uniq='.$plugin[uniq].'">نصب</a>)';
			$config = '-';
		}
?>
			<tr>
				<td class="first"><?=$plugin[name].'<br />'.$plugin[description]?></td>
				<td align="center"><?=$config?></td>
				<td align="center"><? if ($plugin[author][url]) { ?><a href="<?=$plugin[author][url]?>"><?=$plugin[author][name]?></a><? } elseif ($plugin[author][email]) { ?><a href="<?=$plugin[author][email]?>"><?=$plugin[author][name]?></a><? } else { ?><?=$plugin[author][name]?><? } ?></td>
				<td class="last" align="center"><?=$status?></td>
			</tr>
<?
	}
?>
	
	
		</table>
	</div>
<?
}