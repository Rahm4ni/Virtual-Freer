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
	$page = 'category';
	
	$data[category_parent_id]			= $post[parent_id];
	$data[category_title]				= $post[title];
	$data[category_order]				= $post[order];
	$data[category_creator]				= $user_id;
	$data[category_time]				= $now;
	$data[category_ip]					= $server[REMOTE_ADDR];
	
if ($request[action] == 'add')//------------------------------------------------------------- دسته جدید
{
	if ($post[post])
	{
		if (!$post[title])
			$error 	.= 'عنوان دسته را وارد کنید.<br />';
		if (!$error)
		{
			//-------------------------------------- ورود اطلاعات
			$sql 		= $db->queryInsert('category', $data);
			$db->execute($sql);
			$category_id = mysql_insert_id();
			//-------------------------------------- آپلود تصویر در صورت موجود و معتبر بودن
			if (($files['image']['size'] != 0) AND (getimagesize($files['image']['tmp_name'])))
			{
				$target = $config[MainInfo][path].$config[MainInfo][upload][image].'category_'.$category_id.'-'.basename($files[image][name]);
				if(move_uploaded_file($files['image']['tmp_name'], $target))
				{
					$image[category_image]	= $category_id.'-'.basename($files[image][name]);
					$sql 					= $db->queryUpdate('category', $image, 'WHERE `category_id` = "'.$category_id.'" LIMIT 1');
					$db->execute($sql);
				}
			}
			
			header("location:?action=edit&id=$category_id&add=1");
			exit;
		}
	}
	//---------------------------------------- نمایش فرم دسته جدید
	include 'template/header.php';
	category_form($data);
	include 'template/footer.php';
}
elseif (($request[action] == 'edit') AND (check_category_exist($request[id])))//------------- ویرایش دسته
{
	if ($post[post])
	{
		if (!$post[title])
			$error 	.= 'عنوان دسته را وارد کنید.<br />';
		if (!$error)
		{
			//-------------------------------------- ویرایش اطلاعات دسته
			$sql = $db->queryUpdate('category', $data, 'WHERE `category_id` = "'.$request[id].'" LIMIT 1;');
			$db->execute($sql);
			//-------------------------------------- ویرایش تصویر دسته در صورت وجود و معتبر بودن
			if (($files['image']['size'] != 0) AND (getimagesize($files['image']['tmp_name'])))
			{
				$category_image	= $db->retrieve('category_image','category','category_id',$request[id]);
				if ($category_image)
					unlink($config[MainInfo][path].$config[MainInfo][upload][image].'category_'.$category_image);
				
				$target = $config[MainInfo][path].$config[MainInfo][upload][image].'category_'.$request[id].'-'.basename($files[image][name]);
				if(move_uploaded_file($files['image']['tmp_name'], $target))
				{
					$image[category_image]	= $request[id].'-'.basename($files[image][name]);
					$sql 					= $db->queryUpdate('category', $image, 'WHERE `category_id` = "'.$request[id].'" LIMIT 1');
					$db->execute($sql);
				}
			}
			//-------------------------------------- حذف تصویر در صورت تیک‌خوردن
			if ($post[delImage] == 1)
			{
				$category_image	= $db->retrieve('category_image','category','category_id',$request[id]);
				if ($category_image)
					unlink($config[MainInfo][path].$config[MainInfo][upload][image].'category_'.$category_image);
				$update[category_image]	= '';
				$sql = $db->queryUpdate('category', $update, 'WHERE `category_id` = "'.$request[id].'" LIMIT 1;');
				$db->execute($sql);
			}
			
			header("location:?action=edit&id=$request[id]&edit=1");
			exit;
		}
		else
		{
			$sql	= 'SELECT `category_image` FROM `category` WHERE `category_id` = "'.$request[id].'" ORDER BY category_id LIMIT 1;';
			$image	= $db->fetch($sql);
			$data[category_image] = $image[category_image];
		}
	}
	else
	{
		$sql	= 'SELECT * FROM `category` WHERE `category_id` = "'.$request[id].'" ORDER BY category_id LIMIT 1;';
		$data	= $db->fetch($sql);
	}
	
	//---------------------------------------- نمایش فرم ویرایش دسته
	include 'template/header.php';
	category_form($data,$extraFileds);
	include 'template/footer.php';
}
elseif ($request[action] == 'list')//-------------------------------------------------------- نمایش لیست دسته ها
{
	if ($post[del])//------------------------- اگه فرم حذف دسته ارسال شده باشه
	{
		foreach ($post[del] as $category_id)
		{
			//---
			$sql 			= 'SELECT * FROM `category` WHERE `category_parent_id` = "'.$category_id.'";';
			$subcategories	= $db->fetchAll($sql);
			if ($subcategories)
			{
				foreach ($subcategories as $subcategory)
				{
					//-------------------------------------- حذف تصویردسته در صورت وجود
					$category_image	= $db->retrieve('category_image','category','category_id',$subcategory[category_id]);
					if ($category_image)
						unlink($config[MainInfo][path].$config[MainInfo][upload][image].'category_'.$category_image);
					//-------------------------------------- حذف دسته
					$sql			= 'DELETE FROM `category` WHERE `category_id` = "'.$subcategory[category_id].'";';
					$db->execute($sql);
				}
			}
			//-------------------------------------- حذف تصویردسته در صورت وجود
			$category_image		= $db->retrieve('category_image','category','category_id',$category_id);
			if ($category_image)
				unlink($config[MainInfo][path].$config[MainInfo][upload][image].'category_'.$category_image);
			//-------------------------------------- حذف دسته
			$sql			= 'DELETE FROM `category` WHERE `category_id` = "'.$category_id.'";';
			$db->execute($sql);
		}
		header("Location:?action=list&delete=1");
		exit;
	}
	if ($post[orderit])//---------------------اگه فرم چینش دسته ارسال شده باشه
	{
		$n = count($post[id]);
		for ($i=0;$i<$n;$i++)
		{
			$sql	= 'UPDATE `category` SET category_order = "'.$post[order][$i].'" WHERE `category_id` = "'.$post[id][$i].'";';
			$db->execute($sql);
		}
		header("Location:?action=list&save=1");
		exit;
	}
	//---------------------------------------- نمایش لیست دسته ها
	include 'template/header.php';
	category_list ();
	include 'template/footer.php';
	exit;

}
else
{
	header('location:?action=list');
	exit;
}

//------------------------------------------------------------- تابع های محلی
//------------------------------- چاپ فرم اطلاعات دسته
function category_form ($data) {
	global $config;
	$query = mysql_query("SELECT * FROM category ORDER BY category_order,category_id");
	while($r=mysql_fetch_object($query))
	{
		$parent_data[$r->category_parent_id][] = $r;
	}
	if ($data[category_id]) 
	{
?>
	<div class="top-bar">
	<a href="?action=add" class="button">دسته جدید </a> 
		<h1>ویرایش دسته</h1>
		<div class="breadcrumbs"><a href="index.php">خانه</a> / <a href="categories.php">دسته‌ها</a> / ویرایش دسته</div>
	</div><br />
	<div class="select-bar">
	</div>
<?
	}
	else
	{
?>
	<div class="top-bar">
		<h1>دسته جدید</h1>
		<div class="breadcrumbs"><a href="index.php">خانه</a> / <a href="categories.php">دسته‌ها</a> / دسته جدید</div>
	</div><br />
	<div class="select-bar">
	</div>

<?
	}
	include 'template/notify.php';
?>
<form method="post" action="" enctype="multipart/form-data">
	<dt class="title"><label for="title">عنوان دسته:</label></dt>
	<dt><input type="text" name="title" id="title" class="field form" value="<?=$data[category_title]?>" size="60"></dt>
	<dt class="title"><label for="parent_id">مکان دسته :</label></dt>
	<dt><select name="parent_id" size="10" class="field form" style="width: 385px">
			<option value="0" <? if ($data[category_parent_id] == 0) echo 'selected'; ?>>دسته اصلی</option>
			<?=get_cat_option($parent_data,$data[category_parent_id],$data[category_id])?>
	</select></dt>
	<dt class="title"><label for="order">ترتیب:</label></dt>
	<dt><input type="text" name="order" id="order" class="field form" value="<?=$data[category_order]?>" size="4"></dt>
	<dt class="title"><label for="image">تصویر:</label></dt>
	<dt><input type="file" name="image" class="file form" size="47"><br />
<?
 	if ($data[category_image]) 
	{
?>
	<input type="checkbox" name="delImage" value="1">حذف [<a href="<?=$config[MainInfo][url].$config[MainInfo][upload][image].'category_'.$data[category_image]?>" class="colorbox">مشاهده</a>] نام فایل: <span class="filename"><?=$data[category_image]?></span>
<?
	}
	elseif ($data[category_id])
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

//------------------------------- چاپ فرم لیست دسته ها
function category_list ()
{
	global $config;
	$query = mysql_query("SELECT * FROM category ORDER BY category_order,category_id");
	while($r=mysql_fetch_object($query))
	{
		$parent_data[$r->category_parent_id][] = $r;
	}
	$categories = get_cat_list($parent_data);
	if (!$categories)
		$categories = '<td colspan="3">دسته‌ای تعریف نشده است.</td>';
?>
	<div class="top-bar">
	<a href="?action=add" class="button">دسته جدید </a> 
		<h1>دسته‌ها</h1>
		<div class="breadcrumbs"><a href="index.php">خانه</a> / دسته‌ها</div>
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
				<th class="first">عنوان</th>
				<th width="50" align="center">ترتیب</th>
				<th class="last" width="40" align="center">حذف</th>
			</tr>
			<?=$categories;?>
			<tr>
				<td align="left" colspan="3"><input type="hidden" name="post" value="1"><input type="submit" name="orderit" value="چینش" class="button"> <input type="submit" name="delete" value="حذف" class="button"></td>
			</tr>
		</table>
	</div>
</form>
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

//--------------------------------
function get_cat_list($data, $parent = 0 )
{
	static $i = 1;
	$tab = str_repeat(" ",$i);
	static $a = 0;
	$pusher = '<img src="../statics/image/pusher.gif"><img src="../statics/image/pusher.gif">';
	$showPusher = str_repeat($pusher,$a);
	if($data[$parent])
	{
		$html = "$tab";
		$i++;
		foreach($data[$parent] as $v)
		{
			$a++;
			$child = get_cat_list($data, $v->category_id);
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
			$html .= "$tab";
			$html .= '<tr><td class="first">'.$showPusher.' <a href="?action=edit&id='.$v->category_id.'">'.$v->category_title.'</a></td><td align="center"><input type="hidden" name="id[]" value="'.$v->category_id.'"><input type="text" name="order[]" value="'.$v->category_order.'" size="1"></td><td align="center"><input type="checkbox" name="del[]" value="'.$v->category_id.'"></td></tr>'."\n";
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