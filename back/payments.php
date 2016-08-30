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
	$page = 'payments';
	
if (($request[action] == 'view') AND (check_payment_exist($request[id])))//-------------------------------------- نمایش جزئیات
{
	$sql 	= 'SELECT * FROM `payment` JOIN `product` ON  `payment`.`payment_product` = `product`.`product_id` WHERE `payment_id` = "'.$request[id].'" ORDER BY payment_id LIMIT 1;';
	$data 	= $db->fetch($sql);
	
	if($data['product_provider'] == 'parsyar')
	{
		include_once('../include/libs/parsyar.class.php');
		$parsyar = new parsyar();
		$data['parsyar'] = $parsyar->follow_up($request[id]);
	}

	include 'template/header.php';
	payment_form($data);
	include 'template/footer.php';
}
elseif ($request[action] == 'list')//--------------------------------------------------------------------------------------- لیست پرداخت‌ها
{
	if ($post[del])//اگه فرم حذف ارسال شده باشه
	{
		foreach ($post[del] as $payment_id)
		{
			//--------------------------------------- حذف پرداخت
			$sql		= 'DELETE FROM `payment` WHERE `payment_id` = "'.$payment_id.'";';
			$db->execute($sql);
		}
		header('Location:?action=list&delete=1');
		exit;
	}
	
	$data[limit]		= $request[limit];
	$data[page]			= $request[page];
	if (($data[limit] == "") OR (!$data[limit]))
		$data[limit] 	= $config[payment][enrtyPerPage];
	if (($data[page] == "") OR (!$data[page]))
		$data[page] 	= 1;
	$start				= $data[limit]*($data[page]-1);
	
	if ($request[type] == 'search')
	{
		$query			= 'SELECT * FROM `payment` WHERE `payment_email` LIKE "%'.$request[keyword].'%" OR `payment_mobile` LIKE "%'.$request[keyword].'%" OR `payment_amount` LIKE "%'.$request[keyword].'%" OR `payment_res_num` LIKE "%'.$request[keyword].'%" OR `payment_ref_num` LIKE "%'.$request[keyword].'%" ORDER BY payment_id DESC LIMIT '.$start.', '.$data[limit].';';
		$count_query	= 'SELECT COUNT(*) FROM `payment` WHERE `payment_email` LIKE "%'.$request[keyword].'%" OR `payment_mobile` LIKE "%'.$request[keyword].'%" OR `payment_amount` LIKE "%'.$request[keyword].'%" OR `payment_res_num` LIKE "%'.$request[keyword].'%" OR `payment_ref_num` LIKE "%'.$request[keyword].'%" ';
	}
	elseif ($request[type] == 'date')
	{
		if($post[from])
		{
			$time_from = explode('/',$post[from]);
			$time_from = pmktime(0,0,0,$time_from[1],$time_from[0],$time_from[2]);
		}
		else
		{
			$time_from = 0;
		}
		
		if($post[to])
		{
			$time_to = explode('/',$post[to]);
			$time_to = pmktime(23,59,59,$time_to[1],$time_to[0],$time_to[2]);
		}
		else
		{
			$time_to = time();
		}
		
		$query			= 'SELECT * FROM `payment` WHERE `payment_amount` != "0" AND `payment_time` >= '.$time_from.' AND `payment_time` <= '.$time_to.' ORDER BY payment_id DESC LIMIT '.$start.', '.$data[limit].';';
		$count_query	= 'SELECT COUNT(*) FROM `payment` WHERE `payment_amount` != "0"AND `payment_time` >= '.$time_from.' AND `payment_time` <= '.$time_to;
	}
	else
	{
		if ($request[type] == 'success')
			$type_query = " AND `payment_status` = '2'";
		elseif ($request[type] == 'faild')
			$type_query = " AND `payment_status` = '1'";
		else
			$type_query = "";
		
		if ($request[gateway])
			$gateway_query = " AND `payment_gateway` = '$request[gateway]'";
		
		$query			= 'SELECT * FROM `payment` WHERE `payment_amount` != "0" '.$type_query.$gateway_query.' ORDER BY payment_id DESC LIMIT '.$start.', '.$data[limit].';';
		$count_query	= 'SELECT COUNT(*) FROM `payment` WHERE `payment_amount` != "0"'.$type_query.$gateway_query;
	}
	$payments				= $db->fetchAll($query);
	$count_payment			= $db->fetch($count_query);
	$data[total_payment]	= $count_payment['COUNT(*)'];
	$data[last_page] 		= ceil($data[total_payment]/$data[limit]);
	
	include 'template/header.php';
	payment_list ($payments,$data);
	include 'template/footer.php';
	exit;
}
elseif($request[action] == 'settle')//------------------------------------------- ستل دستی بانک ملت
{
	if($post)
	{
		if (!$post[termID])
			$error 	.= 'شماره ترمینال را وارد کنید.<br />';
		if (!$post[username])
			$error 	.= 'نام کاربری را وارد کنید.<br />';
		if (!$post[password])
			$error 	.= 'رمز عبور را وارد کنید.<br />';
		if (!$post[orderID])
			$error 	.= 'شماره سفارش (orderID) را وارد کنید.<br />';
		if (!$post[ReferenceId])
			$error 	.= 'رسید دیجیتالی سفارش (ReferenceId) را وارد کنید.<br />';
		if(!$error)
		{
			include_once('../include/libs/nusoap.php');
			$parameters = array(
				'terminalId' 		=> $post[termID],
				'userName' 			=> $post[username],
				'userPassword' 		=> $post[password],
				'orderId' 			=> $post[orderID],
				'saleOrderId' 		=> $post[orderID],
				'saleReferenceId' 	=> $post[ReferenceId]
			);
			
			$client = new nusoap_client('https://pgws.bpm.bankmellat.ir/pgwchannel/services/pgw?wsdl');
			$namespace='http://interfaces.core.sw.bps.com/';
			
			// Call the SOAP method
			$result = $client->call('bpSettleRequest', $parameters, $namespace);
			
			// Check for a fault
			if ($client->fault)
			{
				foreach ($result as $error)
				$response .= $error;
			}
			else
			{
				$resultStr = $result;
				$err = $client->getError();
				if ($err)
				{
					$response = $err;
				} 
				else 
				{
					// Note: Successful Settle means that sale is settled.
					$response = $resultStr;
				}// end Display the result
			}// end Check for errors
		}
		$response = check_mellat_state_error($response);
	}
	include 'template/header.php';
	settle_form ($post,$response);
	include 'template/footer.php';
	exit;
}
else
{
	header('location:?action=list');
	exit;
}


//------------------------------------------------------------- Local Functions
//------------------------------- چاپ جزئیات محصول
function payment_form ($data) {
	global $config,$db;
	
	$query	= 'SELECT * FROM `card` WHERE `card_payment_id` = "'.$data[payment_id].'" ORDER BY card_id DESC;';
	$cards	= $db->fetchAll($query);
?>
	<div class="top-bar">
		<h1>جزئیات پرداخت</h1>
		<div class="breadcrumbs"><a href="index.php">خانه</a> / <a href="payments.php">پرداخت‌ها</a> / جزئیات پرداخت</div>
	</div><br />
	<div class="select-bar">
	</div>
<?
	include 'template/notify.php';
	$data[payment_gateway] = $db->retrieve('plugin_name','plugin','plugin_uniq',$data[payment_gateway]);
	if ($data[payment_status] == 1) $data[payment_status] = 'کامل نشده'; else $data[payment_status] = 'کامل شده';
?>
	<dt class="title">محصول:</dt>
	<dt><?=$data[product_title]?></dt>
	<dt class="title">ایمیل پرداخت کننده:</dt>
	<dt><?=$data[payment_email]?></dt>
	<dt class="title">شماره همراه پرداخت کننده:</dt>
	<dt><?=$data[payment_mobile]?></dt>
	<dt class="title">مبلغ:</dt>
	<dt><?=Convertnumber2farsi($data[payment_amount])?> ریال</dt>
	<dt class="title">دروازه پرداخت:</dt>
	<dt><?=$data[payment_gateway]?></dt>
	<dt class="title">شناسه پرداخت۱:</dt>
	<dt><?=$data[payment_res_num]?></dt>
	<dt class="title">شناسه پرداخت۲</dt>
	<dt><?=$data[payment_ref_num]?></dt>
	<dt class="title">وضعیت:</dt>
	<dt><?=$data[payment_status]?></dt>
	<dt class="title">زمان:</dt>
	<dt><?=Convertnumber2farsi(pdate('d F Y ساعت G:i',$data[payment_time]))?></dt>
	<dt class="title">آی‌‌پی‌:</dt>
	<dt><?=$data[payment_ip]?></dt>
<?
	if(isset($data[parsyar]))
	{
		if($data[parsyar]->type == 'pin')
		{
?>
	<dt class="title">کارت‌ها:</dt>
<?
			foreach($data[parsyar]->products as $products)
			{
?>
			<dt><? echo $products->serial . ':' . $products->pin?></dt>
<?
			}
		}
		elseif($data[parsyar]->type == 'topup')
		{
?>
	<dt class="title">کد تراکنش تاپ آپ:</dt>
	<dt><? echo $data[parsyar]->products[0]->topup_transaction_code?></dt>
	<dt class="title">شماره شارژ شده:</dt>
	<dt><? echo $data[parsyar]->mobile?></dt>
	<dt class="title">مبلغ شارژ:</dt>
	<dt><? echo convertnumber2farsi($data[parsyar]->products[0]->price)?> ریال</dt>
<?
		}
	}

	if ($cards)
	{
?>
	<dt class="title">کارت‌های خریداری شده‌:</dt>
	<dt>
<?
		foreach($cards as $card)
			echo ' <a href="cards.php?action=edit&id='.$card[card_id].'">#'.$card[card_id].'</a> ';
?>
	</dt>
<?
	}
}

//------------------------------- چاپ لیست محصولات
function payment_list ($payments,$data)
{
	global $db,$request,$user_type,$role,$config;
	if ($data[total_payment] > $data[limit])
	{
		if ($request[type] == 'search' AND $request[keyword])
		{
			$extra = '&type=search&keyword='.$request[keyword];
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
		<h1>پرداخت‌ها</h1>
		<div class="breadcrumbs"><a href="index.php">خانه</a> / پرداخت‌ها</div>
	</div><br />
	<div class="select-bar">
	<b>جستجو:</b><br />
	   <form method="post" action="payments.php"><input type="text" name="keyword" value="<?=$request[keyword]?>"> <input type="hidden" name="action" value="list"><input type="hidden" name="type" value="search"><input type="submit" value="بگرد" class="button"></form>
		<br />
		<b>انتخاب بازه:</b><br />
	   <form method="post" action="payments.php">از تاریخ: <input type="text" id="datepicker12from" dir="ltr" name="from" value="<?=$request[from]?>"/> تا تاریخ: <input type="text" id="datepicker12to" dir="ltr" name="to" value="<?=$request[to]?>"/><input type="hidden" name="action" value="list"><input type="hidden" name="type" value="date"> <input type="submit" value="نمایش" class="button"></form>
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
				<th class="first">دروازه پرداخت</th>
				<th width="100" align="center">مبلغ</th>
				<th width="130" align="center">شناسه۱</th>
				<th width="130" align="center">شناسه۲</th>
				<th width="35" align="center">جزئیات</th>
				<th class="last" width="20" align="center"><input type="checkbox" onclick="checkAll(document.getElementById('form'), 'delet', this.checked);" /></th>
			</tr>
<?
		if ($payments) {
			foreach ($payments as $payment)
			{
				$payment[payment_gateway] = $db->retrieve('plugin_name','plugin','plugin_uniq',$payment[payment_gateway]);
?>
			<tr>
				<td class="first"><?=$payment[payment_gateway]?></td>
				<td><?=Convertnumber2farsi($payment[payment_amount])?> ریال</td>
				<td><?=$payment[payment_res_num]?></td>
				<td><?=$payment[payment_ref_num]?></td>
				<td align="center"><a href="?action=view&id=<?=$payment[payment_id]?>">نمایش</a></td>
				<td class="last" align="center"><input type="checkbox" name="del[]" value="<?=$payment[payment_id]?>" class="delet"></td>
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
		<td colspan="6">پرداختی یافت نشد.</td>
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
//------------------------------- فرم ستل دستی
function settle_form ($data,$response) {
	global $config;
?>
	<div class="top-bar">
		<h1>ستل دستی بانک ملت</h1>
		<div class="breadcrumbs"><a href="index.php">خانه</a> / <a href="payments.php">پرداخت‌ها</a> / ستل دستی بانک ملت</div>
	</div><br />
	<div class="select-bar">
	</div>

<?
	include 'template/notify.php';
?>
<form method="post" action="">
	<dt class="title"><label for="termID">شماره ترمینال:</label></dt>
	<dt><input type="text" name="termID" id="termID" class="field form" value="<?=$data[termID]?>" size="60" dir="ltr"></dt>
	<dt class="title"><label for="username">نام کاربری:</label></dt>
	<dt><input type="text" name="username" id="username" class="field form" value="<?=$data[username]?>" size="60" dir="ltr"></dt>
	<dt class="title"><label for="password">رمز عبور:</label></dt>
	<dt><input type="text" name="password" id="password" class="field form" value="<?=$data[password]?>" size="60" dir="ltr"></dt>
	<dt class="title"><label for="orderID">شماره سفارش (orderID):</label></dt>
	<dt><input type="text" name="orderID" id="orderID" class="field form" value="<?=$data[orderID]?>" size="60" dir="ltr"></dt>
	<dt class="title"><label for="ReferenceId">رسید دیجیتالی سفارش (ReferenceId):</label></dt>
	<dt><input type="text" name="ReferenceId" id="ReferenceId" class="field form" value="<?=$data[ReferenceId]?>" size="60" dir="ltr"></dt>
	<input type="hidden" name="post" value="1" />
	<input type="submit" name="submit" value="انجام ستل" class="button">
</form>
<?
	if ($response)
	{
?>
	<dt class="title">نتیجه ستل</dt>
	<dt><?=$response?></dt>
<?
	}
}
