<?
/*
  Virtual Freer
  http://freer.ir/virtual

  Copyright (c) 2011 Mohammad Hossein Beyram, freer.ir

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v3 (http://www.gnu.org/licenses/gpl-3.0.html)
  as published by the Free Software Foundation.
*/
//------------------ Load Configuration
include 'include/configuration.php';
//------------------ Start Smarty
include 'include/startSmarty.php';

	$sql		= 'SELECT * FROM `payment` WHERE `payment_rand` = "'.$request['random'].'" LIMIT 1;';
	$payment	= $db->fetch($sql);
	
	$query	= 'SELECT * FROM `config` WHERE `config_id` = "1" LIMIT 1';
	$conf	= $db->fetch($query);
	if ($payment AND $payment['payment_time']>($now-(60*$config['card']['reserveExpire'])))
	{
		//-- اطلاعات لازم از جدول payment
		$data['invoice_id']	= $payment['payment_rand'];
		$data['amount'] 		= $payment['payment_amount'];
		$data['callback'] 	= $config['MainInfo']['url'].'callback.php?gateway='.$payment['payment_gateway'];
		//-- اطلاعات لازم از پلاگین
		require_once('plugins/'.$payment['payment_gateway'].'.php');
		$sql			= 'SELECT * FROM `plugindata` WHERE `plugindata_uniq` = "'.$payment['payment_gateway'].'";';
		$plugindatas	= $db->fetchAll($sql);
		if ($plugindatas)
			foreach($plugindatas as $plugindata)
			{
				$data[$plugindata['plugindata_field_name']] = $plugindata['plugindata_field_value'];
			}
		call_user_func('gateway__'.$payment['payment_gateway'],$data);
	}
	elseif($payment)
	{
		$data['title'] = 'خطای سیستم';
		$data['message'] = '<font color="red">زمان تکمیل این سفارش پایان یافته است٬ لطفا دوباره مراحل را از ابتدا شروع کنید.</font><br /><a href="index.php" class="button">بازگشت</a>';
		$smarty->assign('config', $conf);
		$smarty->assign('data', $data);
		$smarty->display('message.tpl');
	}
	else
	{
		$data['title'] = 'خطای سیستم';
		$data['message'] = '<font color="red">سفارشی با این مشخصات یافت نشد.</font><br /><a href="index.php" class="button">بازگشت</a>';
		$smarty->assign('config', $conf);
		$smarty->assign('data', $data);
		$smarty->display('message.tpl');
	}
