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
	$page = 'products';
	
	$data[product_title]				= $post[title];
	$data[product_body]					= $post[body];
	$data[product_first_field_title]	= $post[first_field_title];
	$data[product_second_field_title]	= $post[second_field_title];
	$data[product_third_field_title]	= $post[third_field_title];
	$data[product_price]				= $post[price];
	$data[product_provider]				= $post[provider];
	$data[product_product_id]			= $post[product_id];
	$data[product_category]				= $post[category];
	$data[product_ip]					= $server[remote_address];
	
if (($request[action] == 'delete') AND (check_product_exist($request[id])))//------------------- حذف محصول
{
	//-------------------------------------- حذف تصویر محصول
	$product_image	= $db->retrieve('product_image','product','product_id',$request[id]);
	if ($product_image)
		unlink($config[MainInfo][path].$config[MainInfo][upload][image].'product_'.$product_image);
	//-------------------------------------- حذف محصول
	$sql	= 'DELETE FROM `product` WHERE `product_id` = "'.$request[id].'" LIMIT 1;';
	$db->execute($sql);
	
	header('location:?action=list&delete=1');
	exit;
}
elseif ($request[action] == 'add')//--------------------------------------------------------- محصول جدید
{
	if ($post[post])
	{
		if (!$post[title])
			$error 	.= 'عنوان محصول را وارد کنید.<br />';
		if ($post[provider] != 'parsyar'  OR ($post[provider] == 'parsyar' AND ($post[product_id] < 20 OR $post[product_id] > 23)))
		{
			if (!$post[price])
				$error 	.= 'قیمت محصول را وارد کنید.<br />';
			elseif (filter_var($post[price], FILTER_VALIDATE_INT)== false)
				$error 	.= 'قیمت فقط باید شامل اعداد باشد.<br />';
		}
		if (!$post[category])
			$error 	.= 'دسته محصول را انتخاب کنید.<br />';
		if (!$post[provider])
			$error 	.= 'تامین کننده را انتخاب کنید.<br />';
		if ($post[provider] != 'db' AND $post[provider] != '' AND !$post[product_id])
			$error 	.= 'کد محصول تامین کننده را وارد کنید.<br />';
		if (!$error)
		{
			$data[product_time] = $now;
			//-------------------------------------- ورود اطلاعات
			$sql 		= $db->queryInsert('product', $data);
			$db->execute($sql);
			$product_id = mysql_insert_id();
			//-------------------------------------- آپلود تصویر در صورت موجود و معتبر بودن
			if (($files['image']['size'] != 0) AND (getimagesize($files['image']['tmp_name'])))
			{
				$target = $config[MainInfo][path].$config[MainInfo][upload][image].'product_'.$product_id.'-'.basename($files[image][name]);
				if(move_uploaded_file($files['image']['tmp_name'], $target))
				{
					$image[product_image]	= $product_id.'-'.basename($files[image][name]);
					$sql 					= $db->queryUpdate('product', $image, 'WHERE `product_id` = "'.$product_id.'" LIMIT 1');
					$db->execute($sql);
				}
			}
			header('location:?action=edit&id='.$product_id.'&add=1');
			exit;
		}
	}
	include 'template/header.php';
	product_form($data);
	include 'template/footer.php';
}
elseif (($request[action] == 'edit') AND (check_product_exist($request[id])))//-------------------------------------- ویرایش محصول
{
	if ($post[post])
	{
		if (!$post[title])
			$error 	.= 'عنوان محصول را وارد کنید.<br />';
		if ($post[provider] != 'parsyar'  OR ($post[provider] == 'parsyar' AND ($post[product_id] < 20 OR $post[product_id] > 23)))
		{
			if (!$post[price])
				$error 	.= 'قیمت محصول را وارد کنید.<br />';
			elseif (filter_var($post[price], FILTER_VALIDATE_INT)== false)
				$error 	.= 'قیمت فقط باید شامل اعداد باشد.<br />';
		}
		if (!$post[category])
			$error 	.= 'دسته محصول را انتخاب کنید.<br />';
		if (!$post[provider])
			$error 	.= 'تامین کننده را انتخاب کنید.<br />';
		if ($post[provider] != '' AND $post[provider] != 'db'  AND !$post[product_id])
			$error 	.= 'کد محصول تامین کننده را وارد کنید.<br />';
		if (!$error)
		{
			//-------------------------------------- ویرایش اطلاعات
			$sql = $db->queryUpdate('product', $data, 'WHERE `product_id` = "'.$request[id].'" LIMIT 1;');
			$db->execute($sql);
			//-------------------------------------- آپلود تصویر در صورت موجود و معتبر بودن
			if (($files['image']['size'] != 0) AND (getimagesize($files['image']['tmp_name'])))
			{
				$target = $config[MainInfo][path].$config[MainInfo][upload][image].'product_'.$request[id].'-'.basename($files[image][name]);
				if(move_uploaded_file($files['image']['tmp_name'], $target))
				{
					$image[product_image]	= $request[id].'-'.basename($files[image][name]);
					$sql 					= $db->queryUpdate('product', $image, 'WHERE `product_id` = "'.$request[id].'" LIMIT 1');
					$db->execute($sql);
				}
			}
			//-------------------------------------- حذف تصویر در صورت تیک‌خوردن
			if ($post[delImage] == 1)
			{
				$product_image	= $db->retrieve('product_image','product','product_id',$request[id]);
				if ($product_image)
					unlink($config[MainInfo][path].$config[MainInfo][upload][image].'product_'.$product_image);
				$update[product_image]	= '';
				$sql = $db->queryUpdate('product', $update, 'WHERE `product_id` = "'.$request[id].'" LIMIT 1;');
				$db->execute($sql);
			}
			header('location:?action=edit&id='.$request[id].'&edit=1');
			exit;
		}
		else
		{
			$sql	= 'SELECT `product_image` FROM `product` WHERE `product_id` = "'.$request[id].'" ORDER BY product_id LIMIT 1;';
			$image	= $db->fetch($sql);
			$data[product_image] = $image[product_image];
		}
	}
	else
	{
		$sql 	= 'SELECT * FROM `product` WHERE `product_id` = "'.$request[id].'" ORDER BY product_id LIMIT 1;';
		$data 	= $db->fetch($sql);
	}
	include 'template/header.php';
	product_form($data);
	include 'template/footer.php';
}
elseif ($request[action] == 'list')//--------------------------------------------------------------------------------------- لیست محصولات
{
	if ($post[del])//اگه فرم حذف ارسال شده باشه
	{
		foreach ($post[del] as $product_id)
		{
			//-------------------------------------- حذف تصویر محصول
			$product_image	= $db->retrieve('product_image','product','product_id',$product_id);
			if ($product_image)
				unlink($config[MainInfo][path].$config[MainInfo][upload][image].'product_'.$product_image);
			//--------------------------------------- حذف محصول
			$sql		= 'DELETE FROM `product` WHERE `product_id` = "'.$product_id.'";';
			$db->execute($sql);
		}
		header('Location:?action=list&delete=1');
		exit;
	}
	
	$data[limit]		= $request[limit];
	$data[page]			= $request[page];
	if (($data[limit] == "") OR (!$data[limit]))
		$data[limit] 	= $config[product][enrtyPerPage];
	if (($data[page] == "") OR (!$data[page]))
		$data[page] 	= 1;
	$start				= $data[limit]*($data[page]-1);
	
	$query			= "SELECT * FROM `product` ORDER BY product_id DESC LIMIT $start, $data[limit];";
	$count_query	= "SELECT COUNT(*) FROM `product`";
	
	$products				= $db->fetchAll($query);
	$count_product			= $db->fetch($count_query);
	$data[total_product]	= $count_product['COUNT(*)'];
	$data[last_page] 		= ceil($data[total_product]/$data[limit]);
	
	include 'template/header.php';
	product_list ($products,$data);
	include 'template/footer.php';
	exit;
}
else
{
	header('location:?action=list');
	exit;
}


//------------------------------------------------------------- Local Functions
//------------------------------- چاپ فرم ورود اطلاعات محصول
function product_form ($data) {
	global $config;
	$query = mysql_query("SELECT * FROM category ORDER BY category_order,category_id");
	while($r=mysql_fetch_object($query))
	{
		$parent_data[$r->category_parent_id][] = $r;
	}
	if ($data[product_id]) 
	{
?>
	<div class="top-bar">
	<a href="?action=add" class="button">محصول جدید </a> <a href="?action=delete&id=<?=$data[product_id]?>" onclick="if(confirm('مطمئن هستید که می‌خواهید این محصول را حذف کنید؟ ')){return true}else{return false}" class="button">حذف محصول </a> 
		<h1>ویرایش محصول</h1>
		<div class="breadcrumbs"><a href="index.php">خانه</a> / <a href="products.php">محصولات</a> / ویرایش محصول</div>
	</div><br />
	<div class="select-bar">
	</div>
<?
	}
	else
	{
?>
	<div class="top-bar">
		<h1>محصول جدید</h1>
		<div class="breadcrumbs"><a href="index.php">خانه</a> / <a href="products.php">محصولات</a> / محصول جدید</div>
	</div><br />
	<div class="select-bar">
	</div>

<?
	}
	include 'template/notify.php';
?>
<form method="post" action="" enctype="multipart/form-data">
	<dt class="title"><label for="title">عنوان محصول:</label></dt>
	<dt><input type="text" name="title" id="title" class="field form" value="<?=$data[product_title]?>" size="60"></dt>
	<dt class="title"><label for="body">توضیحات محصول (در موقع تحویل):</label></dt>
	<dt><textarea name="body" id="body" rows="3" cols="57" class="field form"><?=$data[product_body]?></textarea></dt>
	<dt class="title"><label for="first_field_title">عنوان فیلد اول:</label></dt>
	<dt><input type="text" name="first_field_title" id="first_field_title" class="field form" value="<?=$data[product_first_field_title]?>" size="60"></dt>
	<dt class="title"><label for="second_field_title">عنوان فیلد دوم:</label></dt>
	<dt><input type="text" name="second_field_title" id="second_field_title" class="field form" value="<?=$data[product_second_field_title]?>" size="60"></dt>
	<dt class="title"><label for="third_field_title">عنوان فیلد سوم:</label></dt>
	<dt><input type="text" name="third_field_title" id="third_field_title" class="field form" value="<?=$data[product_third_field_title]?>" size="60"></dt>
	<dt class="title"><label for="price">قیمت:</label></dt>
	<dt><input type="text" name="price" id="price" class="field form" value="<?=$data[product_price]?>" size="6" dir="ltr"> ریال</dt>
	<dt class="title"><label for="category">دسته :</label></dt>
	<dt><select name="category" id="category" size="1" class="field form" style="width: 325px">
			<option value="">--</option>
			<?=get_cat_option($parent_data,$data[product_category])?>
	</select></dt>
	<dt class="title"><label for="provider">تامین کننده: </label></dt>
	<dt><select name="provider" id="provider" size="1" class="field form" style="width: 325px">
		<option value="">--</option>
		<option value="db"<? if($data[product_provider] == 'db') echo ' selected'; ?>>دیتابیس محلی</option>
		<option value="parsyar"<? if($data[product_provider] == 'parsyar') echo ' selected'; ?>>پارس‌یار</option>
	</select></dt>
	<div id="remote_product_id">
		<dt class="title"><label for="product_id">کد محصول: </label></dt>
		<dt><input type="text" name="product_id" id="product_id" class="field form" value="<?=$data[product_product_id]?>" size="6" dir="ltr"></dt>
	</div>

<script>
    $(document).ready(function()
    {
        provider();
        $('select#provider').change(function ()
        {
            provider();
        });
        function provider()
        {
            var provider = $("select#provider option:selected").val();
            
            if(provider != 'db' && provider != '')
            {
				$('div[id="remote_product_id"]').slideDown();
			}
			else
			{
				$('div[id="remote_product_id"]').slideUp();
			}
			
		}
    });
</script>
	<dt class="title"><label for="image">تصویر:</label></dt>
	<dt><input type="file" name="image" class="file form" size="47"><br />
<?
 	if ($data[product_image]) 
	{
?>
	<input type="checkbox" name="delImage" value="1">حذف [<a href="<?=$config[MainInfo][url].$config[MainInfo][upload][image].'product_'.$data[product_image]?>" class="colorbox">مشاهده</a>] نام فایل: <span class="filename"><?=$data[product_image]?></span>
<?
	}
	elseif ($data[product_id])
	{
?>
			تصویری انتخاب نشده است.
<?
	}
?>
    </dt>
	<input type="hidden" name="post" value="1" />
	<input type="submit" name="submit" value="ذخیره" class="button">
</form>
<?
}

//------------------------------- چاپ لیست محصولات
function product_list ($products,$data)
{
	global $db,$request,$user_type,$role,$config;
	$query = mysql_query("SELECT * FROM category ORDER BY category_order,category_id");
	while($r=mysql_fetch_object($query))
	{
		$parent_data[$r->category_parent_id][] = $r;
	}
	if ($data[total_product] > $data[limit])
	{
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
					$pagenation .= '<option value="?action=list&limit='.$data[limit].'&page='.$i.'" selected>'.Convertnumber2farsi($i).'</option>';
				}
				else
				{
					$pagenation .= '<option value="?action=list&limit='.$data[limit].'&page='.$i.'">'.Convertnumber2farsi($i).'</option>';
				}
			}
		$pagenation .= '</select></form>';
	}
?>
	<div class="top-bar">
	<a href="?action=add" class="button">محصول جدید </a>
		<h1>محصولات</h1>
		<div class="breadcrumbs"><a href="index.php">خانه</a> / محصولات</div>
	</div><br />
	<div class="select-bar">
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
				<th class="first">نام</th>
				<th width="160" align="center">دسته</th>
				<th width="60" align="center">قیمت</th>
				<th width="35" align="center">عملیات</th>
				<th class="last" width="20" align="center"><input type="checkbox" onclick="checkAll(document.getElementById('form'), 'delet', this.checked);" /></th>
			</tr>
<?
		if ($products) {
			foreach ($products as $product)
			{
				$product[product_category] = $db->retrieve('category_title','category','category_id',$product[product_category]);
?>
			<tr>
				<td class="first"><?=$product[product_title]?></td>
				<td><?=$product[product_category]?></td>
				<td><?=Convertnumber2farsi($product[product_price])?> ریال</td>
				<td align="center"><a href="?action=edit&id=<?=$product[product_id]?>">ویرایش</a></td>
				<td class="last" align="center"><input type="checkbox" name="del[]" value="<?=$product[product_id]?>" class="delet"></td>
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
		<td colspan="5">محصولی یافت نشد.</td>
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

//--------------------------------
function get_cat_option($data, $pid, $cid=0, $parent=0)
{
	static $i = 1;
	$tab = str_repeat(" ",$i);
	static $a = 0;
	$pusher = "-";
	$showPusher = str_repeat($pusher,$a);
	if($data[$parent])
	{
		$html = "$tab";
		$i++;
		foreach($data[$parent] as $v)
		{
			$a++;
			$child = get_cat_option($data, $pid, $cid, $v->category_id);
			if($v->category_parent_id == 0)
			{
				$listChild = "";
			}
			if($v->category_id == $pid)
			{
				$selected = ' selected';
			}
			else
			{
				$selected = '';
			}
			if($v->category_id == $cid)
			{
				$disabled = ' disabled';
			}
			else
			{
				$disabled = '';
			}
			$html .= "$tab";
			$html .= '<option value="'.$v->category_id.'"'.$selected.$disabled.'>'.$showPusher.' '.$v->category_title.'</option>';
			$a--;
			if($child)
			{
				$i--;
				$html .= $child;
				$html .= "$tab";
			}
		}
		$html .= "$tab";
		return $html;
	}
	else
	{
		return false;
	}
}
