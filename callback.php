<?
/*
  Virtual Freer
  http://freer.ir/virtual

  Copyright (c) 2011 Mohammad Hossein Beyram, freer.ir

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v3 (http://www.gnu.org/licenses/gpl-3.0.html)
  as published by the Free Software Foundation.
*/
$page = 'callback';
//------------------ Load Configuration
include 'include/configuration.php';
//------------------ Start Smarty
include 'include/startSmarty.php';
	$query	= 'SELECT * FROM `config` WHERE `config_id` = "1" LIMIT 1';
	$conf	= $db->fetch($query);
	
	if ($request['gateway'])
	{
		$retrieve_plugin_type	= $db->retrieve('plugin_type','plugin','plugin_uniq',$request['gateway']);
		if ($retrieve_plugin_type == 'payment')
		{
			//-- اطلاعات لازم از پلاگین
			require_once('plugins/'.$request['gateway'].'.php');
			$sql			= 'SELECT * FROM `plugindata` WHERE `plugindata_uniq` = "'.$request['gateway'].'";';
			$plugindatas	= $db->fetchAll($sql);
			if ($plugindatas)
			{
				$data = null;
				foreach($plugindatas as $plugindata)
				{
					$data[$plugindata['plugindata_field_name']] = $plugindata['plugindata_field_value'];
				}
			}
			$output = call_user_func('callback__'.$request['gateway'],$data);
			
			//-- پرداخت موفقیت آمیز بود
			if($output['status'] == 1)
			{
				//-- اطلاعات فکتور پرداخت
				$sql		= 'SELECT * FROM `payment` WHERE `payment_id` = "'.$output['payment_id'].'";';
				$payment	= $db->fetch($sql);
				
				//-- اطلاعات محصول
				$sql		= 'SELECT * FROM `product` WHERE `product_id` = "'.$payment['payment_product'].'";';
				$product	= $db->fetch($sql);
				
				if($product['product_provider'] == 'parsyar')
				{
					include_once('include/libs/parsyar.class.php');
					$parsyar = new parsyar();
					if($product['product_product_id'] >= 20 AND $product['product_product_id'] <= 28)
					{
						//Topup
						$resp = $parsyar->buy_topup($output['payment_id'],$product['product_product_id'],$payment['payment_amount'],$payment['payment_mobile']);
						if($resp->error_code != '-1')
						{
							//-- نمایش پیغام خطا
							$data['title'] = 'خطای سیستم';
							if($resp->error)
							{
								$data['message'] = '<font color="red">'.$resp->error.'</font><br /><a href="index.php" class="button">بازگشت</a>';
							}
							elseif($resp->error_code)
							{
								$data['message'] = '<font color="red">خطایی به وجود آمده است٬ کد خطا: '.$resp->error_code.'</font><br /><a href="index.php" class="button">بازگشت</a>';
							}
							$smarty->assign('config', $conf);
							$smarty->assign('data', $data);
							$smarty->display('message.tpl');
							exit;
						}
						else
						{
							if(is_array($resp->products))
							{
								foreach($resp->products as $card):
									$cards[] = array(
										'card_product' => $card->product,
										'card_first_field' => $card->topup_transaction_code,
										'card_second_field' => $card->price,
										'card_third_field' => $resp->mobile
									);
								endforeach;
							}
							else
							{
								$cards = null;
							}
						}
					}
					else
					{
						//pin
						$resp = $parsyar->buy_pin($output['payment_id'],$product['product_product_id'],$payment['payment_qty']);
						if($resp->error_code != '-1')
						{
							//-- نمایش پیغام خطا
							$data['title'] = 'خطای سیستم';
							if($resp->error)
							{
								$data['message'] = '<font color="red">'.$resp->error.'</font><br /><a href="index.php" class="button">بازگشت</a>';
							}
							elseif($resp->error_code)
							{
								$data['message'] = '<font color="red">خطایی به وجود آمده است٬ کد خطا: '.$resp->error_code.'</font><br /><a href="index.php" class="button">بازگشت</a>';
							}
							$smarty->assign('config', $conf);
							$smarty->assign('data', $data);
							$smarty->display('message.tpl');
							exit;
						}
						else
						{
							if(isset($resp->products) AND is_array($resp->products))
							{
								foreach($resp->products as $card):
									$cards[] = array(
										'card_product' => $card->product,
										'card_first_field' => $card->pin,
										'card_second_field' => $card->serial,
										'card_third_field' => null
									);
								endforeach;
							}
							else
							{
								$cards = null;
							}
						}
					}
				}
				else
				{
					//-- اطلاعات کارت ها
					$sql		= 'SELECT *,DECODE(card_first_field,"'.$config['databaseInfo']['salt'].'") as card_first_field ,DECODE(card_second_field,"'.$config['databaseInfo']['salt'].'") as card_second_field ,DECODE(card_third_field,"'.$config['databaseInfo']['salt'].'") as card_third_field FROM `card` WHERE `card_payment_id` = "'.$output['payment_id'].'";';
					$cards		= $db->fetchAll($sql);
					
					//-- به‌روز رسانی و نمایش کارت					
					$update['card_customer_email']	= $payment['payment_email'];
					$update['card_customer_mobile']	= $payment['payment_mobile'];
					$update['card_payment_res_num']	= $output['res_num'];
					$update['card_payment_ref_num']	= $output['ref_num'];
					$update['card_payment_gateway']	= $request['gateway'];
					$update['card_payment_time']		= $now;
					$update['card_status']			= 2;
					$sql = $db->queryUpdate('card', $update, 'WHERE `card_payment_id` = "'.$output['payment_id'].'";');
					$db->execute($sql);
					unset($update);
				}
				//--بروز رسانی پرداخت
				$update['payment_status']		= 2;
				$update['payment_res_num']	= $output['res_num'];
				$update['payment_ref_num']	= $output['ref_num'];
				
				$sql = $db->queryUpdate('payment', $update, 'WHERE `payment_id` = "'.$output['payment_id'].'" LIMIT 1;');
				$db->execute($sql);
				
				//-- پلاگین‌های اطلاع رسانی
				$sql		= 'SELECT * FROM `plugin` WHERE `plugin_type` = "notify" AND `plugin_status` = "1";';
				$plugins	= $db->fetchAll($sql);
				if($plugins)
					foreach($plugins as $plugin)
					{
						require_once('plugins/'.$plugin['plugin_uniq'].'.php');
						
						$sql			= 'SELECT * FROM `plugindata` WHERE `plugindata_uniq` = "'.$plugin['plugin_uniq'].'";';
						$plugindatas	= $db->fetchAll($sql);
						if ($plugindatas)
							foreach($plugindatas as $plugindata)
							{
								$data[$plugindata['plugindata_field_name']] = $plugindata['plugindata_field_value'];
							}
						call_user_func('notify__'.$plugin['plugin_uniq'],$data,$output,$payment,$product,$cards);
						unset($data);
					}
				//-- ارسال اطلاعات به اسمارتی
				$smarty->assign('config', $conf);
				$smarty->assign('cards', $cards);
				$smarty->assign('product', $product);
				$smarty->assign('output', $output);
				$smarty->display('callback.tpl');
				exit;
			}
			else
			{
				//-- نمایش پیغام خطا
				$data['title'] = 'خطا';
				if ($output['message'])
					$data['message'] = $output['message'];
				else
					$data['message'] = '<font color="red">در بازگشت از بانک مشکلی به وجود آمد٬ لطفا دوباره سعی کنید.</font><br /><a href="index.php" class="button">بازگشت</a>';
				$smarty->assign('config', $conf);
				$smarty->assign('data', $data);
				$smarty->display('message.tpl');
				exit;
			}
		}
		else
		{
				//-- نمایش پیغام خطا
				$data['title'] = 'خطای سیستم';
				$data['message'] = '<font color="red">چنین دروازه پرداختی وجود ندارد.</font><br /><a href="index.php" class="button">بازگشت</a>';
				$smarty->assign('config', $conf);
				$smarty->assign('data', $data);
				$smarty->display('message.tpl');
				exit;
		}
	}
	else
	{
			//-- نمایش پیغام خطا
			$data['title'] = 'خطای سیستم';
			$data['message'] = '<font color="red">اطلاعات پرداخت ناقص است.</font><br /><a href="index.php" class="button">بازگشت</a>';
			$smarty->assign('config', $conf);
			$smarty->assign('data', $data);
			$smarty->display('message.tpl');
			exit;
	}
