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
	$page = 'statistics';

if ($request[action] == 'report')//---------------------------------- لیست آمار
{
	if($post[post])
	{
		$time_from = explode('/',$post[from]);
		$time_from = pmktime(0,0,0,$time_from[1],$time_from[0],$time_from[2]);
		
		$time_to = explode('/',$post[to]);
		$time_to = pmktime(23,59,59,$time_to[1],$time_to[0],$time_to[2]);
		
		$query	= 'SELECT *,DECODE(card_first_field,"'.$config[databaseInfo][salt].'") as card_first_field ,DECODE(card_second_field,"'.$config[databaseInfo][salt].'") as card_second_field ,DECODE(card_third_field,"'.$config[databaseInfo][salt].'") as card_third_field FROM `card` WHERE `card_payment_time` > "'.$time_from.'" AND `card_payment_time` < "'.$time_to.'" AND `card_status` = "2" ORDER BY card_payment_time DESC;';
		$cards	= $db->fetchAll($query);
	}
	include 'template/header.php';
	report ($cards);
	include 'template/footer.php';
	exit;
}
elseif ($request[action] == 'list')//---------------------------------- لیست آمار
{
	include 'template/header.php';
	statistics ();
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
function statistics ()
{
	global $config,$db;
?>
	<div class="top-bar">
		<h1>آمار</h1>
		<div class="breadcrumbs"><a href="index.php">خانه</a> / <a href="statistics.php">آمار</a> / آمار کارت‌ها</div>
	</div><br />
	<div class="select-bar">
	</div>
<?	include 'template/notify.php';	?>
	<div class="table">
		<img src="../statics/image/bg-th-left.gif" width="8" height="7" alt="" class="left" />
		<img src="../statics/image/bg-th-right.gif" width="7" height="7" alt="" class="right" />
		<table class="listing" cellpadding="0" cellspacing="0">
			<tr>
				<th class="first">محصول</th>
				<th width="130" align="center">دسته</th>
				<th width="60" align="center">قیمت</th>
				<th width="60" align="center">قابل فروش</th>
				<th width="65" align="center">فروخته شده</th>
				<th width="40" align="center">مخفی</th>
			</tr>
		<?
			$query		= "SELECT * FROM `product` ORDER BY product_id DESC;";
			$products	= $db->fetchAll($query);
			if ($products) {
				foreach ($products as $product)
				{
					$product[product_category] = $db->retrieve('category_title','category','category_id',$product[product_category]);
					
					$count_query= 'SELECT COUNT(*) FROM `card` WHERE `card_product` = "'.$product[product_id].'" AND `card_status` = "1" AND `card_show` = "1"';
					$count_card	= $db->fetch($count_query);
					$available	= $count_card['COUNT(*)'];
					
					$count_query= 'SELECT COUNT(*) FROM `card` WHERE `card_product` = "'.$product[product_id].'" AND `card_status` = "2"';
					$count_card	= $db->fetch($count_query);
					$sold		= $count_card['COUNT(*)'];
					
					$count_query= 'SELECT COUNT(*) FROM `card` WHERE `card_product` = "'.$product[product_id].'" AND `card_show` = "2"';
					$count_card	= $db->fetch($count_query);
					$invisiable	= $count_card['COUNT(*)'];
?>
			<tr>
				<td class="first"><?=$product[product_title]?></td>
				<td><?=$product[product_category]?></td>
				<td><?=Convertnumber2farsi($product[product_price])?> ریال</td>
				<td align="center"><?=Convertnumber2farsi($available)?></td>
				<td align="center"><?=Convertnumber2farsi($sold)?></td>
				<td class="last" align="center"><?=Convertnumber2farsi($invisiable)?></td>
			</tr>
<?
				}
			}
			else
			{
?>
			<tr>
				<td colspan="7">محصولی پیدا نشد.</td>
			</tr>
<?
			}
?>
		</table>
	</div>
<?
}

//------------------------------- گزارش فروش دیتابیس محلی
function report ($cards)
{
	global $config,$db,$post;
?>
	<div class="top-bar">
	<h1>گزارش فروش دیتابیس محلی</h1>
		<div class="breadcrumbs"><a href="index.php">خانه</a> / <a href="statistics.php">آمار</a> / گزارش فروش دیتابیس محلی</div>
	</div>
	<div class="select-bar-border">
	<b>انتخاب بازه:</b><br />
		   <form method="post">از تاریخ: <input type="text" id="datepicker12from" dir="ltr" name="from" value="<?=$post[from]?>"/> تا تاریخ: <input type="text" id="datepicker12to" dir="ltr" name="to" value="<?=$post[to]?>"/><input type="hidden" name="post" value="1"> <input type="submit" value="نمایش" class="button"></form>
	</div>
<?	include 'template/notify.php';
	if (!$post[post])
	{
?>
یک بازه زمانی را مشخص کنید.
<?	}
	elseif (!$post[from] OR !$post[to])
	{
?>
بازه زمانی را به صورت کامل مشخص کنید.
<?
	}
	else
	{
?>
<form method="post" id="form">
	<div class="table">
		<img src="../statics/image/bg-th-left.gif" width="8" height="7" alt="" class="left" />
		<img src="../statics/image/bg-th-right.gif" width="7" height="7" alt="" class="right" />
		<table class="listing" cellpadding="0" cellspacing="0">
			<tr>
				<th class="first">فیلد اول</th>
				<th>فیلد دوم</th>
				<th>فیلد سوم</th>
				<th align="center">دسته</th>
				<th width="100" align="center">تاریخ فروش</th>
				<th class="last" width="40" align="center">عملیات</th>
			</tr>
<?
		if ($cards) {
			foreach ($cards as $card)
			{
				$total += $db->retrieve('product_price','product','product_id',$card[card_product]);
				$card[card_product] = $db->retrieve('product_title','product','product_id',$card[card_product]);
				$card[card_payment_time] = Convertnumber2farsi(pdate('Y/m/d G:i',$card[card_payment_time]));
?>
			<tr>
				<td class="first"><?=$card[card_first_field]?></td>
				<td><?=$card[card_second_field]?></td>
				<td><?=$card[card_third_field]?></td>
				<td align="center"><?=$card[card_product]?></td>
				<td align="center"><?=$card[card_payment_time]?></td>
				<td class="last" align="center"><a href="?action=edit&id=<?=$card[card_id]?>">ویرایش</a></td>
			</tr>
<?
			}
?>
			<tr>
				<td colspan="4" align="left">مجموع فروش دوره: </td>
				<td colspan="2" align="right"><?=Convertnumber2farsi($total)?> ریال</td>
			</tr>
<?
		} else {
?>
	<tr>
		<td colspan="6">فروشی یافت نشد.</td>
	</tr>
<?
		}
?>
		</table>
	</form>
<?
	if ($pagenation)
	{
?>
		<div class="select">
			<strong>صفحات: </strong>
			<?=$pagenation?>
		</div>
<?
	}
?>
	</div>
<?
	}
}
