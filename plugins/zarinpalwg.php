<?
/*
  Virtual Freer
  http://freer.ir/virtual

  Copyright (c) 2011 Mohammad Hossein Beyram, freer.ir

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v3 (http://www.gnu.org/licenses/gpl-3.0.html)
  as published by the Free Software Foundation.
*/
	//-- اطلاعات کلی پلاگین
	$pluginData[zarinpalwg][type] = 'payment';
	$pluginData[zarinpalwg][name] = 'زرین پال - وب گیت';
	$pluginData[zarinpalwg][uniq] = 'zarinpalwg';
	$pluginData[zarinpalwg][description] = 'مخصوص پرداخت با دروازه پرداخت <a href="http://zarinpal.com">زرین‌پال‌</a>';
	$pluginData[zarinpalwg][author][name] = 'Freer';
	$pluginData[zarinpalwg][author][url] = 'http://freer.ir';
	$pluginData[zarinpalwg][author][email] = 'hossin@gmail.com';
	
	//-- فیلدهای تنظیمات پلاگین
	$pluginData[zarinpalwg][field][config][1][title] = 'مرچنت';
	$pluginData[zarinpalwg][field][config][1][name] = 'merchant';
	$pluginData[zarinpalwg][field][config][2][title] = 'عنوان خرید';
	$pluginData[zarinpalwg][field][config][2][name] = 'title';
	
	//-- تابع انتقال به دروازه پرداخت
	function gateway__zarinpalwg($data)
	{
		global $config,$db,$smarty;
		include_once('include/libs/nusoap.php');
		$merchantID 	= trim($data[merchant]);
		$amount 		= round($data[amount]/10);
		$invoice_id		= $data[invoice_id];
		$callBackUrl 	= $data[callback];
		
		$client = new nusoap_client('https://www.zarinpal.com/pg/services/WebGate/wsdl', 'wsdl');
		$client->soap_defencoding = 'UTF-8';
		$res = $client->call("PaymentRequest", array(
			array(
				'MerchantID' 	=> $merchantID,
				'Amount' 	=> $amount,
				'Description' 	=> $data[title].' - '.$data[invoice_id],
				'Email' 	=> $data[email],
				'Mobile' 	=> $data[mobile],
				'CallbackURL' 	=> $callBackUrl
			)
		));
	
		if ($res['Status'] == 100)
		{
			$update[payment_rand]		= $res[Authority];
			$sql = $db->queryUpdate('payment', $update, 'WHERE `payment_rand` = "'.$invoice_id.'" LIMIT 1;');
			$db->execute($sql);
			header('location:https://www.zarinpal.com/pg/StartPay/' . $res['Authority']);
			exit;
		}
		else
		{
			$data[title] = 'خطای سیستم';
			$data[message] = '<font color="red">در اتصال به درگاه زرین‌پال مشکلی به وجود آمد٬ لطفا از درگاه سایر بانک‌ها استفاده نمایید.</font>'.$res['Status'].'<br /><a href="index.php" class="button">بازگشت</a>';
			$query	= 'SELECT * FROM `config` WHERE `config_id` = "1" LIMIT 1';
			$conf	= $db->fetch($query);
			$smarty->assign('config', $conf);
			$smarty->assign('data', $data);
			$smarty->display('message.tpl');
		}
	}
	
	//-- تابع بررسی وضعیت پرداخت
	function callback__zarinpalwg($data)
	{
		global $db,$get;
		$Authority 	= $get['Authority'];
		$ref_id = $get['refID'];
		if ($_GET['Status'] == 'OK')
		{
			include_once('include/libs/nusoap.php');
			$merchantID = $data[merchant];
			$sql 		= 'SELECT * FROM `payment` WHERE `payment_rand` = "'.$Authority.'" LIMIT 1;';
			$payment 	= $db->fetch($sql);
			
			$amount		= round($payment[payment_amount]/10);
			$client = new nusoap_client('https://www.zarinpal.com/pg/services/WebGate/wsdl', 'wsdl');
			$res = $client->call("PaymentVerification", array(
				array(
					'MerchantID'	 => $merchantID,
					'Authority' 	 => $Authority,
					'Amount'	 => $amount
				)
			));
			if ($payment[payment_status] == 1)
			{
				if ($res['Status'] == 100)//-- موفقیت آمیز
				{
					//-- آماده کردن خروجی
					$output[status]		= 1;
					$output[res_num]	= $Authority;
					$output[ref_num]	= $res['RefID'];
					$output[payment_id]	= $payment[payment_id];
				}
				else
				{
					//-- در تایید پرداخت مشکلی به‌وجود آمده است‌
					$output[status]	= 0;
					$output[message]= 'پرداخت توسط زرین‌پال تایید نشد‌.'.$res['Status'];
				}
			}
			else
			{
				//-- قبلا پرداخت شده است‌
				$output[status]	= 0;
				$output[message]= 'سفارش قبلا پرداخت شده است.';
			}
		}
		else
		{
			//-- شماره یکتا اشتباه است
			$output[status]	= 0;
			$output[message]= 'شماره یکتا اشتباه است.';
		}
		return $output;
	}
