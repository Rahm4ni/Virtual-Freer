<?
/*
  Virtual Freer
  http://freer.ir/virtual

  Copyright (c) 2011 Mohammad Hossein Beyram, freer.ir

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v3 (http://www.gnu.org/licenses/gpl-3.0.html)
  as published by the Free Software Foundation.
*/
	$config[MainInfo][title] = $db->retrieve('config_site_title','config','config_id',1);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>مدیریت سایت <?=$config[MainInfo][title]?></title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<script src="../statics/js/jquery.js" type="text/javascript"></script>
	<!---------- colorbox ---------->
	<link media="screen" rel="stylesheet" href="../statics/css/colorbox.css" />
	<script src="../statics/js/jquery.colorbox.js"></script>
	<script>
		$(document).ready(function(){
			$(".colorbox").colorbox();
			$("#click").click(function(){ 
				$('#click').css({"background-color":"#f00", "color":"#fff", "cursor":"inherit"});
				return false;
			});
		});
	</script>
	<!---------- /colorbox ---------->
	<!---------- Date picker ---------->
	<link type="text/css" href="../statics/css/jquery-ui-1.8.14.css" rel="stylesheet" />
	<script type="text/javascript" src="../statics/js/jquery.ui.datepicker-cc.all.min.js"></script>
    <script type="text/javascript">
	    $(function() {
	        $('#datepicker12from').datepicker({
	            onSelect: function(dateText, inst) {
	                $('#datepicker12to').datepicker('option', 'minDate', new JalaliDate(inst['selectedYear'], inst['selectedMonth'], inst['selectedDay']));
	            }
	        });
	        $('#datepicker12to').datepicker();
	    });
    </script>
	<!---------- /Date picker ---------->
	<style media="all" type="text/css">@import "../statics/css/all.css";</style>
</head>
<body>
<div id="main">
	<div id="header">
		<a href="index.php" class="logo"><img src="../statics/image/logo.png" alt="" /></a>
		<ul id="top-navigation">
		<?	if (!check_login())	{	?>
			<li class="active"><span><span>ورود</span></span></li>
		<?	}	else	{	?>
			<li<? if ($page=='index') echo ' class="active"';?>><span><span><? if ($page=='index'){ ?>صفحه نخست<? } else { ?><a href="index.php">صفحه نخست</a><? } ?></span></span></li>
			<li<? if ($page=='category') echo ' class="active"';?>><span><span><? if ($page=='category'){ ?>دسته‌ها<? } else { ?><a href="categories.php">دسته‌ها</a><? } ?></span></span></li>
			<li<? if ($page=='products') echo ' class="active"';?>><span><span><? if ($page=='products'){ ?>محصولات<? } else { ?><a href="products.php">محصولات</a><? } ?></span></span></li>
			<li<? if ($page=='card') echo ' class="active"';?>><span><span><? if ($page=='card'){ ?>کارت‌ها<? } else { ?><a href="cards.php">کارت‌ها</a><? } ?></span></span></li>
			<li<? if ($page=='payments') echo ' class="active"';?>><span><span><? if ($page=='payments'){ ?>پرداخت‌ها<? } else { ?><a href="payments.php">پرداخت‌ها</a><? } ?></span></span></li>
			<li<? if ($page=='setting') echo ' class="active"';?>><span><span><? if ($page=='setting'){ ?>تنظیمات<? } else { ?><a href="setting.php">تنظیمات</a><? } ?></span></span></li>
			<li<? if ($page=='statistics') echo ' class="active"';?>><span><span><? if ($page=='statistics'){ ?>آمار<? } else { ?><a href="statistics.php">آمار</a><? } ?></span></span></li>
			<li<? if ($page=='plugins') echo ' class="active"';?>><span><span><? if ($page=='plugins'){ ?>افزونه‌ها<? } else { ?><a href="plugins.php">افزونه‌ها</a><? } ?></span></span></li>
			<li<? if ($page=='logout') echo ' class="active"';?>><span><span><? if ($page=='logout'){ ?>خروج<? } else { ?><a href="logout.php">خروج</a><? } ?></span></span></li>
		<?	}	?>
		</ul>
	</div>
	<div id="middle">
		<div id="left-column">
<?
	if (check_login())	{
		if ($page == 'category') { ?>
			<h3>دسته‌ها</h3>
			<ul class="nav">
				<li><a href="?action=list">لیست دسته‌ها</a></li>
				<li><a href="?action=add">دسته جدید</a></li>
			</ul>
<?		} elseif ($page == 'products') { ?>
			<h3>محصولات</h3>
			<ul class="nav">
				<li><a href="?action=list">لیست محصولات</a></li>
				<li><a href="?action=add">محصول جدید</a></li>
			</ul>
<?		} elseif ($page == 'card') { ?>
			<h3>کارت‌ها</h3>
			<ul class="nav">
				<li><a href="?action=list">لیست کارت‌ها</a></li>
				<li><a href="?action=add&type=form">کارت جدید</a></li>
				<li><a href="?action=add&type=file">آپلود فایل</a></li>
			</ul>
<?		} elseif ($page == 'payments') { ?>
			<h3>پرداخت‌ها</h3>
			<ul class="nav">
				<li><a href="?action=list">همه پرداخت‌ها</a></li>
				<li><a href="?action=list&type=success">پرداخت‌های کامل شده</a></li>
				<li><a href="?action=list&type=faild">پرداخت‌های کامل نشده</a></li>
<?
				$query		= 'SELECT * FROM `plugin` WHERE `plugin_type` = "payment" AND `plugin_status` = "1" ORDER BY `plugin_id`;';
				$plugins	= $db->fetchAll($query);
				if ($plugins)
					foreach ($plugins as $plugin)
					{
?>
				<li><a href="?action=list&gateway=<?=$plugin[plugin_uniq]?>"><?=$plugin[plugin_name]?></a></li>
<?
				if($plugin[plugin_uniq] == 'mellat')
					echo '<li><a href="?action=settle">ستل دستی بانک ملت</a></li>';
				}
?>
			</ul>
<? 		} elseif ($page == 'setting') { ?>
			<h3>تنظیمات</h3>
			<ul class="nav">
				<li><a href="?action=main">تنظیمات اصلی</a></li>
<?
		$sql		= 'SELECT * FROM `plugin` WHERE `plugin_status` = "1" ORDER BY `plugin_id` ASC;';
		$plugins	= $db->fetchAll($sql);
		if ($plugins)
			foreach($plugins as $plugin)
			{
?>
				<li><a href="?plugin=<?=$plugin[plugin_uniq]?>"><?=$plugin[plugin_name]?></a></li>
<?
			}
?>
			</ul>
<? 		} elseif ($page == 'statistics') { ?>
			<h3>آمار</h3>
			<ul class="nav">
				<li><a href="?action=list">آمار کارت‌ها</a></li>
				<li><a href="?action=report">گزارش فروش</a></li>
			</ul>
<?		} else {	?>
			<h3>منو</h3>
			<ul class="nav">
				<li><a href="categories.php">دسته‌ها</a></li>
				<li><a href="products.php">محصولات</a></li>
				<li><a href="cards.php">کارت‌ها</a></li>
				<li><a href="setting.php">تنظیمات</a></li>
				<li><a href="statistics.php">آمار</a></li>
				<li><a href="plugins.php">افزونه‌ها</a></li>
				<li><a href="http://parsyar.com" target="_blank">پارس‌یار</a></li>
				<li><a href="logout.php">خروج</a></li>
			</ul>
<?
		}
	} else {
?>
			<h3>منو</h3>
			<ul class="nav">
				<li><a href="login.php">ورود</a></li>
			</ul>
<?	}	?>
		
		</div>
		<div id="center-column">