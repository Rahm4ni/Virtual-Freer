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
	$page = 'card';
	
	$data[card_product]		= $post[product];
	$data[card_first_field]	= $post[first_field];
	$data[card_second_field]= $post[second_field];
	$data[card_third_field]	= $post[third_field];
	$data[card_status]		= $post[status];
	$data[card_show]		= $post[show];
	
if (!$session['key'])
{
	if ($post[post])
	{
		//
		if ($post['key'] != $config[databaseInfo][salt])
			$error 		.= 'کلید وارد شده صحیح نیست.<br />';
		if (!$error)
		{
			$_SESSION['key'] = $post['key'];
			header('location:cards.php');
			exit;
		}
	}
	include 'template/header.php';
	card_key($post);
	include 'template/footer.php';
	exit;
}

if (($request[action] == 'delete') AND (check_card_exist($request[id])))//------------------- حذف کارت
{
	//-------------------------------------- حذف کارت
	$sql	= 'DELETE FROM `card` WHERE `card_id` = "'.$request[id].'" LIMIT 1;';
	$db->execute($sql);
	
	header("location:?action=list&delete=1");
	exit;
}
elseif ($request[action] == 'add')//--------------------------------------------------------- کارت جدید
{
	if($request[type] == 'file')//-------------------------------------- ورود اطلاعات از فایل
	{
		if ($post[post])
		{
			if ($files[file][size] == 0)
				$error 		.= 'فایلی انتخاب نکرده‌اید.<br />';
			elseif ($files[file][size] > 10485760)
				$error 		.= 'فایل بیش از حد بزرگ است، حداکثر حجم فایل 10 مگابایت است.<br />';
			if (!$post[separator])
				$error 		.= 'جداکننده را انتخاب کنید.<br />';
			if (!$post[product])
				$error 		.= 'نوع محصول را انتخاب کنید.<br />';
			if (!$error)
			{
				$data[card_time]	= $now;
				if (!$data[card_show])
					$data[card_show] = 2;
				if (!$data[card_status])
					$data[card_status] = 2;
				
				$fp      			= fopen($files[file][tmp_name], 'r');
				$content 			= fread($fp, filesize($files[file][tmp_name]));
				$lines 				= explode("\n", $content);
				foreach($lines as $line)
				{
					$splited 				= explode($post[separator], $line);
					$data[card_first_field]	= trim($splited[0]);
					$data[card_second_field]= trim($splited[1]);
					$data[card_third_field]	= trim($splited[2]);
					//-------------------------------------- ورود اطلاعات
					if ($data[card_first_field] OR $data[card_second_field] OR $data[card_third_field])
					{
						$sql 	= "INSERT INTO `card` SET `card_product` = '$data[card_product]', `card_first_field` = ENCODE('$data[card_first_field]','".$session['key']."'), `card_second_field` = ENCODE('$data[card_second_field]','".$session['key']."'), `card_third_field` = ENCODE('$data[card_third_field]','".$session['key']."'), `card_time` = '$data[card_time]', `card_status` = '$data[card_status]', `card_show` = '$data[card_show]'";
						$db->execute($sql);
					}
				}
				header("location:?action=list&add=1");
				exit;
			}
		}
		include 'template/header.php';
		card_file($data);
		include 'template/footer.php';
	}
	else//-------------------------------------- ورود اطلاعات از فرم
	{
		if ($post[post])
		{
			if (!$post[first_field] AND !$post[second_field] AND !$post[third_field])
				$error 	.= 'حداقل یکی از سه فیلد باید پر شود.<br />';
			if (!$post[product])
				$error 	.= 'محصول را انتخاب کنید.<br />';
			if (!$error)
			{
				$data[card_time] = $now;
				if (!$data[card_show])
					$data[card_show] = 2;
				if (!$data[card_status])
					$data[card_status] = 2;
				//-------------------------------------- ورود اطلاعات
				$sql 		= "INSERT INTO `card` SET `card_product` = '$data[card_product]', `card_first_field` = ENCODE('$data[card_first_field]','".$session['key']."'), `card_second_field` = ENCODE('$data[card_second_field]','".$session['key']."'), `card_third_field` = ENCODE('$data[card_third_field]','".$session['key']."'), `card_time` = '$data[card_time]', `card_status` = '$data[card_status]', `card_show` = '$data[card_show]'";
				$db->execute($sql);
				$card_id	= mysql_insert_id();
				header("location:?action=edit&id=$card_id&add=1");
				exit;
			}
		}
		include 'template/header.php';
		card_form($data);
		include 'template/footer.php';
	}
}
elseif (($request[action] == 'edit') AND (check_card_exist($request[id])))//-------------------------------------- ویرایش کارت
{
	if ($post[post])
	{
		if (!$post[first_field] AND !$post[second_field] AND !$post[third_field])
			$error 	.= 'حداقل یکی از سه فیلد باید پر شود.<br />';
		if (!$post[product])
			$error 	.= 'نوع محصول را انتخاب کنید.<br />';
		if (!$error)
		{
			if (!$data[card_show])
				$data[card_show] = 2;
			if (!$data[card_status])
				$data[card_status] = 2;
			//-------------------------------------- ویرایش اطلاعات
			$sql = 'UPDATE `card` SET `card_product` = "'.$data[card_product].'", `card_first_field` = ENCODE("'.$data[card_first_field].'","'.$session['key'].'"), `card_second_field` = ENCODE("'.$data[card_second_field].'","'.$session['key'].'"), `card_third_field` = ENCODE("'.$data[card_third_field].'","'.$session['key'].'"), `card_status` = "'.$data[card_status].'", `card_show` = "'.$data[card_show].'" WHERE `card_id` = "'.$request[id].'" LIMIT 1;';
			$db->execute($sql);
			
			header("location:?action=edit&id=$request[id]&edit=1");
			exit;
		}
	}
	else
	{
		$sql 	= 'SELECT *,DECODE(card_first_field,"'.$session['key'].'") as card_first_field ,DECODE(card_second_field,"'.$session['key'].'") as card_second_field ,DECODE(card_third_field,"'.$session['key'].'") as card_third_field FROM `card` WHERE `card_id` = "'.$request[id].'" ORDER BY card_id LIMIT 1;';
		$data 	= $db->fetch($sql);
	}
	include 'template/header.php';
	card_form($data);
	include 'template/footer.php';
}
elseif ($request[action] == 'list')//--------------------------------------------------------------------------------------- لیست کارت ها
{
	if ($post[del])//اگه فرم حذف ارسال شده باشه
	{
		foreach ($post[del] as $card_id)
		{
			//--------------------------------------- حذف کارت
			$sql			= 'DELETE FROM `card` WHERE `card_id` = "'.$card_id.'";';
			$db->execute($sql);
		}
		header("Location:?action=list&delete=1");
		exit;
	}
	
	$data[limit]		= $request[limit];
	$data[page]			= $request[page];
	if (($data[limit] == "") OR (!$data[limit]))
		$data[limit] 	= $config[card][enrtyPerPage];
	if (($data[page] == "") OR (!$data[page]))
		$data[page] 	= 1;
	$start				= $data[limit]*($data[page]-1);
	
	if ($request[type] == 'search')
	{
		$query			= 'SELECT *,DECODE(card_first_field,"'.$session['key'].'") as card_first_field ,DECODE(card_second_field,"'.$session['key'].'") as card_second_field ,DECODE(card_third_field,"'.$session['key'].'") as card_third_field FROM `card` WHERE DECODE(card_first_field,"'.$session['key'].'") LIKE "%'.$request[keyword].'%" OR DECODE(card_second_field,"'.$session['key'].'") LIKE "%'.$request[keyword].'%" OR DECODE(card_third_field,"'.$session['key'].'") LIKE "%'.$request[keyword].'%" ORDER BY card_id DESC LIMIT '.$start.', '.$data[limit].';';
		$count_query	= 'SELECT COUNT(*) FROM `card` WHERE DECODE(card_first_field,"'.$session['key'].'") LIKE "%'.$request[keyword].'%" OR DECODE(card_second_field,"'.$session['key'].'") LIKE "%'.$request[keyword].'%" OR DECODE(card_third_field,"'.$session['key'].'") LIKE "%'.$request[keyword].'%"';
	}
	elseif ($request[type] == 'filter')
	{
		if ($request[product] AND $request[product]!='all')
			$product_query	= " AND `card_product` = '$request[product]'";
		
		if (isset($request[status]) AND $request[status]!='all')
			$status_query 	= " AND `card_status` = '$request[status]'";
		
		if (isset($request[show]) AND $request[show]!='all')
			$show_query 	= " AND `card_show` = '$request[show]'";
		
		$query			= "SELECT *,DECODE(card_first_field,'".$session['key']."') as card_first_field ,DECODE(card_second_field,'".$session['key']."') as card_second_field ,DECODE(card_third_field,'".$session['key']."') as card_third_field FROM `card` WHERE 1=1 $product_query $status_query $show_query ORDER BY card_id DESC LIMIT $start, $data[limit];";
		$count_query	= "SELECT COUNT(*) FROM `card` WHERE 1=1 $product_query $status_query $show_query ORDER BY card_id";
	}
	else
	{
		$query			= "SELECT *,DECODE(card_first_field,'".$session['key']."') as card_first_field ,DECODE(card_second_field,'".$session['key']."') as card_second_field ,DECODE(card_third_field,'".$session['key']."') as card_third_field FROM `card` ORDER BY card_id DESC LIMIT $start, $data[limit];";
		$count_query	= "SELECT COUNT(*) FROM `card` ORDER BY card_id";
	}
	$cards				= $db->fetchAll($query);
	$count_card			= $db->fetch($count_query);
	$data[total_card]	= $count_card['COUNT(*)'];
	$data[last_page] 	= ceil($data[total_card]/$data[limit]);
	
	include 'template/header.php';
	card_list ($cards,$data);
	include 'template/footer.php';
	exit;
}
else
{
	header('location:?action=list');
	exit;
}


//------------------------------------------------------------- Local Functions
//------------------------------- چاپ فرم ورود اطلاعات کارت
function card_key ($data) {
	global $config,$post,$db;
?>
	<div class="top-bar">
		<h1>دریافت کلید رمزنگاری</h1>
		<div class="breadcrumbs"><a href="index.php">خانه</a> / <a href="cards.php">کارت‌ها</a> / دریافت کلید رمزنگاری</div>
	</div><br />
	<div class="select-bar">
	</div>
<?
	include 'template/notify.php';
?>
<form method="post" action="">
	<dt class="title"><label for="key">کلید رمزنگاری:</label></dt>
	<dt><input type="text" name="key" id="key" class="field form" value="<?=$data[key]?>" size="60"></dt>
	<input type="hidden" name="post" value="1" />
	<input type="submit" name="submit" value="ارسال" class="button">
</form>
<?
}

//------------------------------- چاپ فرم ورود اطلاعات کارت
function card_form ($data) {
	global $config,$post,$db;
	if ($data[card_id]) 
	{
?>
	<div class="top-bar">
	<a href="?action=delete&id=<?=$data[card_id]?>" class="button" onclick="if(confirm('مطمئن هستید که می‌خواهید این کارت را حذف کنید؟ ')){return true}else{return false}">حذف</a> <a href="?action=add&type=form" class="button">کارت جدید</a> <a href="?action=add&type=file" class="button">آپلود فایل</a>
		<h1>ویرایش کارت</h1>
		<div class="breadcrumbs"><a href="index.php">خانه</a> / <a href="cards.php">کارت‌ها</a> / ویرایش کارت</div>
	</div><br />
	<div class="select-bar">
	</div>
<?
	}
	else
	{
?>
	<div class="top-bar">
	<a href="?action=add&type=file" class="button">آپلود فایل </a>
		<h1>کارت جدید</h1>
		<div class="breadcrumbs"><a href="index.php">خانه</a> / <a href="cards.php">کارت‌ها</a> / کارت جدید</div>
	</div><br />
	<div class="select-bar">
	</div>
<?
	}
	include 'template/notify.php';
?>
<form method="post" action="" enctype="multipart/form-data">
	<dt class="title"><label for="first_field">فیلد اول:</label></dt>
	<dt><input type="text" name="first_field" id="first_field" class="field form" value="<?=$data[card_first_field]?>" size="60"></dt>
	<dt class="title"><label for="second_field">فیلد دوم:</label></dt>
	<dt><input type="text" name="second_field" id="second_field" class="field form" value="<?=$data[card_second_field]?>" size="60"></dt>
	<dt class="title"><label for="third_field">فیلد سوم:</label></dt>
	<dt><input type="text" name="third_field" id="third_field" class="field form" value="<?=$data[card_third_field]?>" size="60"></dt>
	<dt class="title"><label for="product">نوع محصول :</label></dt>
	<dt><select name="product" size="1" class="field form" style="width: 323px">
			<option value="">--</option>
	<?
		$query		= "SELECT * FROM `product` WHERE `product_provider` = 'db' OR `product_provider` = '' ORDER BY product_id ASC;";
		$products	= $db->fetchAll($query);
		if($products)
		{
			foreach($products as $product)
			{
				$product[product_category] = $db->retrieve('category_title','category','category_id',$product[product_category]);
				if ($product[product_id] == $data[card_product]) $selected = 'selected'; else $selected = '';
				echo '<option value="'.$product[product_id].'" '.$selected.'>'.$product[product_title].' ('.$product[product_category].')</option>';
			}
		}
	?>
	</select></dt>
	<dt class="title">تنظیمات:</dt>
	<dt><input type="checkbox" name="status" value="1"<? if ($data[card_status] == 1 OR (!$data[card_id] AND !$post)) echo ' checked'; ?> />قابل فروش</dt>
	<dt><input type="checkbox" name="show" value="1"<? if ($data[card_show] == 1 OR (!$data[card_id] AND !$post)) echo ' checked'; ?> />قابل نمایش</dt>
	<input type="hidden" name="type" value="form" />
	<input type="hidden" name="post" value="1" />
	<input type="submit" name="submit" value="ذخیره" class="button">
</form>
<?
	if ($data[card_status] == 2)
	{
?>
	<dt class="title">ایمیل خریدار:</dt>
	<dt><?=$data[card_customer_email]?></dt>
	<dt class="title">شماره موبایل خریدار:</dt>
	<dt><?=$data[card_customer_mobile]?></dt>
	<dt class="title">شناسه پرداخت ۱:</dt>
	<dt><?=$data[card_payment_res_num]?></dt>
	<dt class="title">شناسه پرداخت ۲:</dt>
	<dt><?=$data[card_payment_ref_num]?></dt>
	<dt class="title">دروازه پرداخت:</dt>
	<dt><?=$data[card_payment_gateway]?></dt>
	<dt class="title">زمان خرید:</dt>
	<dt><?=Convertnumber2farsi(pdate('Y/m/d G:i',$data[card_payment_time]))?></dt>
<?
	}
}

//------------------------------- چاپ فرم آپلود فایل
function card_file ($data) {
	global $config,$post,$db;
?>
	<div class="top-bar">
	<a href="?action=add&type=form" class="button">کارت جدید </a>
		<h1>کارت جدید</h1>
		<div class="breadcrumbs"><a href="index.php">خانه</a> / <a href="cards.php">کارت‌ها</a> / آپلود فایل</div>
	</div><br />
	<div class="select-bar">
	</div>
<?
	include 'template/notify.php';
?>
<form method="post" action="" enctype="multipart/form-data">
	<dt class="title"><label for="file">فایل:</label></dt>
	<dt><input type="file" name="file" class="file form" size="37"><br />
	<dt class="title"><label for="separator">جدا کننده:</label></dt>
	<dt><select name="separator" id="separator" style="width:250px;" class="field form">
			<option value="">--</option>
			<option value=":"<? if ($post[separator] == ':') echo ' selected'; ?>>:</option>
			<option value=";"<? if ($post[separator] == ';') echo ' selected'; ?>>;</option>
			<option value=","<? if($post[separator] == ',')echo ' selected'; ?>>,</option>
			<option value="-"<? if($post[separator] == '-')echo ' selected'; ?>>-</option>
	</select></dt>
	<dt class="title"><label for="product">نوع محصول :</label></dt>
	<dt><select name="product" size="1" class="field form" style="width: 250px">
			<option value="">--</option>
	<?
		$query		= "SELECT * FROM `product` WHERE `product_provider` = 'db' OR `product_provider` = '' ORDER BY product_id ASC;";
		$products	= $db->fetchAll($query);
		if($products)
		{
			foreach($products as $product)
			{
				$product[product_category] = $db->retrieve('category_title','category','category_id',$product[product_category]);
				if ($product[product_id] == $data[card_product]) $selected = 'selected'; else $selected = '';
				echo '<option value="'.$product[product_id].'" '.$selected.'>'.$product[product_title].' ('.$product[product_category].')</option>';
			}
		}
	?>
	</select></dt>
	<dt class="title">تنظیمات:</dt>
	<dt><input type="checkbox" name="status" value="1"<? if ($data[card_status] == 1 OR (!$data[card_id] AND !$post)) echo ' checked'; ?> />قابل فروش</dt>
	<dt><input type="checkbox" name="show" value="1"<? if ($data[card_show] == 1 OR (!$data[card_id] AND !$post)) echo ' checked'; ?> />قابل نمایش</dt>
	<input type="hidden" name="type" value="file" />
	<input type="hidden" name="post" value="1" />
	<input type="submit" name="submit" value="ذخیره" class="button">
</form>
<?
}

//------------------------------- چاپ لیست کارت ها
function card_list ($cards,$data)
{
	global $db,$request,$user_type,$role,$config;
	if ($data[total_card] > $data[limit])
	{
		if ($request[type] == 'search' AND $request[keyword])
		{
			$extra = '&type='.$request[type].'&keyword='.$request[keyword];
		}
		elseif ($request[type] == 'filter')
		{
			if ($request[product] AND $request[product]!='all')
				$product_string = '&product='.$request[product];
			if (isset($request[status]) AND $request[status]!='all')
				$status_string =  '&status='.$request[status];
			if (isset($request[show]) AND $request[show]!='all')
				$show_string =  '&show='.$request[show];
			$extra = '&type='.$request[type].$product_string.$status_string.$show_string;
		}
		$pagenation .= '	<script>
			 function GoTo (c) {
			   if (c != \'--\')
			 window.location = c;
			 }</script>
					<form method="post" name="SelectPage">
					<select size="1" id="PageChoice" class="field form" name="PageChoice" onChange ="GoTo(document.SelectPage.PageChoice.value)">';
		for ($i=1; $i<=$data[last_page]; $i++)
			{
				if ($i == $data[page])
				{
					$pagenation .= '<option value="?action=list'.$extra.'&limit='.$data[limit].'&page='.$i.'" selected>'.Convertnumber2farsi($i).'</option>';
				}
				else
				{
					$pagenation .= '<option value="?action=list'.$extra.'&limit='.$data[limit].'&page='.$i.'">'.Convertnumber2farsi($i).'</option>';
				}
			}
		$pagenation .= '</select></form>';
	}
?>
	<div class="top-bar">
	<a href="?action=add&type=form" class="button">کارت جدید </a> <a href="?action=add&type=file" class="button">آپلود فایل </a>
		<h1>کارت‌ها</h1>
		<div class="breadcrumbs"><a href="index.php">خانه</a> / کارت‌ها</div>
	</div><br />
	<div class="select-bar-border">
	<b>جستجو:</b><br />
		   <form method="post" action="cards.php"><input type="text" name="keyword" value="<?=$request[keyword]?>"> <input type="hidden" name="action" value="list"><input type="hidden" name="type" value="search"><input type="submit" value="بگرد" class="button"></form>
	<br /><b>فیلتر:</b><br />
	<form method="post" action="cards.php">
	محصول: <select name="product" size="1" class="field form" style="width: 200px"><option value="all">همه</option>
	<?
		$query		= "SELECT * FROM `product` ORDER BY product_title ASC;";
		$products	= $db->fetchAll($query);
		if($products)
		{
			foreach($products as $product)
			{
				$product[product_category] = $db->retrieve('category_title','category','category_id',$product[product_category]);
				if ($product[product_id] == $data[card_product]) $selected = 'selected'; else $selected = '';
				echo '<option value="'.$product[product_id].'" '.$selected.'>'.$product[product_title].' ('.$product[product_category].')</option>';
			}
		}
	?></select>
	وضعیت: <select name="status" size="1" class="field form" style="width: 90px"><option value="all">همه</option><option value="1"<? if($request[status] == 1) echo ' selected'; ?>>قابل فروش</option><option value="2"<? if($request[status] == 2) echo ' selected'; ?>>فروخته شده</option></select>
	نمایش: <select name="show" size="1" class="field form" style="width: 70px"><option value="all">همه</option><option value="1"<? if($request[show] == 1) echo ' selected'; ?>>دارد</option><option value="2"<? if($request[show] == 2) echo ' selected'; ?>>ندارد</option></select>
	<input type="hidden" name="action" value="list"><input type="hidden" name="type" value="filter"><input type="submit" value="فیلتر" class="button"></form>
	</div>
<?	include 'template/notify.php';	?>
<script language="JavaScript">
function checkAll(theForm, cName, status) {
for (i=0,n=theForm.elements.length;i<n;i++)
  if (theForm.elements[i].className.indexOf(cName) !=-1) {
    theForm.elements[i].checked = status;
  }
}
</script>
<form method="post" id="form">
	<div class="table">
		<img src="../statics/image/bg-th-left.gif" width="8" height="7" alt="" class="left" />
		<img src="../statics/image/bg-th-right.gif" width="7" height="7" alt="" class="right" />
		<table class="listing" cellpadding="0" cellspacing="0">
			<tr>
				<th class="first">فیلد اول</th>
				<th>فیلد دوم</th>
				<th>فیلد سوم</th>
				<th align="center">محصول</th>
				<th width="60" align="center">وضعیت</th>
				<th width="40" align="center">نمایش</th>
				<th width="35" align="center">عملیات</th>
				<th class="last" width="20" align="center"><input type="checkbox" onclick="checkAll(document.getElementById('form'), 'delet', this.checked);" /></th>
			</tr>
<?
		if ($cards) {
			foreach ($cards as $card)
			{
				$card[card_product] = $db->retrieve('product_title','product','product_id',$card[card_product]);
				if ($card[card_status] == 1) $card[card_status] = 'قابل فروش'; else $card[card_status] = 'فروخته شده';
				if ($card[card_show] == 1) $card[card_show] = 'دارد'; else $card[card_show] = 'ندارد';
?>
			<tr>
				<td class="first"><?=$card[card_first_field]?></td>
				<td><?=$card[card_second_field]?></td>
				<td><?=$card[card_third_field]?></td>
				<td align="center"><?=$card[card_product]?></td>
				<td align="center"><?=$card[card_status]?></td>
				<td align="center"><?=$card[card_show]?></td>
				<td align="center"><a href="?action=edit&id=<?=$card[card_id]?>">ویرایش</a></td>
				<td class="last" align="center"><input type="checkbox" name="del[]" value="<?=$card[card_id]?>" class="delet"></td>
			</tr>
<?
			}
?>
			<tr>
				<td align="left" colspan="8"><input type="hidden" name="post" value="1"><input type="submit" name="delete" value="حذف" class="button"></td>
			</tr>
<?
		} else {
?>
	<tr>
		<td colspan="8">کارتی یافت نشد.</td>
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
