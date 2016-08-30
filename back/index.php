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
if (!check_login())//---------------------------------------------------------------------------------------------------------------------------------------------- Check Login
{
	header('location:logout.php');
	exit;
}
	$page = 'index';
	include 'template/header.php';
?>
	<div class="top-bar">
		<h1>خانه</h1>
		<div class="breadcrumbs">خانه</div>
	</div><br />
	<div class="select-bar">
	</div>
	<table width="100%" cellspacing="10" cellpadding="10">
		<tr>
			<td align="center"><a href="categories.php"><img src="../statics/image/icon/category.png"><br />دسته‌ها</a></td>
			<td align="center"><a href="products.php"><img src="../statics/image/icon/product.png"><br />محصولات</a></td>
			<td align="center"><a href="cards.php"><img src="../statics/image/icon/cart.png"><br />کارت‌ها</a></td>
		</tr>
		<tr>
			<td align="center"><a href="setting.php"><img src="../statics/image/icon/config.png"><br />تنظیمات</a></td>
			<td align="center"><a href="statistics.php"><img src="../statics/image/icon/chart.png"><br />آمار</a></td>
			<td align="center"><a href="payments.php"><img src="../statics/image/icon/payment.png"><br />پرداخت‌ها</a></td>
		<tr>
		<tr>
			<td align="center"></td>
			<td align="center"><a href="plugins.php"><img src="../statics/image/icon/plugin.png"><br />افزونه‌ها</a></td>
			<td align="center"><a href="logout.php"><img src="../statics/image/icon/logout.png"><br />خروج</a></td>
		<tr>
	</table>
<?
	include 'template/footer.php';