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

	if($post AND ($post['action'] != '' OR $get['action'] != '')) 
	{
		if($post['action'] == '')
		{
			$data['action'] 		= isset($get['action']) ? $get['action'] : null;
			$data['card']			= isset($get['card']) ? $get['card'] : null; 
			$data['qty']			= isset($get['qty']) ? $get['qty'] : null; 
			$data['gateway']		= isset($get['gateway']) ? $get['gateway'] : null;
			$data['email']			= isset($get['email']) ? $get['email'] : null;
			$data['mobile']			= isset($get['mobile']) ? '0' . substr($get['mobile'], -10) : null;
			$data['topup_mobile']	= isset($get['topup_mobile']) ? '0' . substr($get['topup_mobile'], -10) : null;
			$data['topup_amount']	= isset($get['topup_amount']) ? $get['topup_amount'] : null;
			$noJavaScript 	= 1;
		} else {
			$data['action'] 		= isset($post['action']) ? $post['action'] : null;
			$data['card']			= isset($post['card']) ? $post['card'] : null;
			$data['qty']			= isset($post['qty']) ? $post['qty'] : null;
			$data['gateway']		= isset($post['gateway']) ? $post['gateway'] : null;
			$data['email']			= isset($post['email']) ? $post['email'] : null;
			$data['mobile']			= isset($post['mobile']) ? '0' . substr($post['mobile'], -10) : null;
			$data['topup_mobile']	= isset($post['topup_mobile']) ? '0' . substr($post['topup_mobile'], -10) : null;
			$data['topup_amount']	= isset($post['topup_amount']) ? $post['topup_amount'] : null;
			$noJavaScript 	= 0;
		}
	}
	
	$error = null;
	if (isset($data) AND $data['action'] == "payit")
	{
		if (!$data['card'])
			$error	.= 'محصولی انتخاب نکرده‌اید.‌<br />';
		else
		{
			$query		= 'SELECT * FROM `product` WHERE `product_id` = "'.$data['card'].'"';
			$product	= $db->fetch($query);
			if(!$product)
			{
				$error	.= 'چنین محصولی وجود ندارد.<br>';
			}
		}
		if (!$data['qty'])
			$error	.= 'تعداد کارت درخواستی مشخص نشده است.‌<br />';
		if ($product AND $data['qty'])
		{
			if($product['product_provider'] == 'parsyar')
			{
				include_once('include/libs/parsyar.class.php');
				$parsyar = new parsyar();
				$balance = $parsyar->get_balance();
				
				$amount_error = 1;
				if($product['product_product_id'] >= 20 AND $product['product_product_id'] <= 28)
				{
					$data['mobile'] = $data['topup_mobile'];
					if (!$data['topup_mobile'])
					{
						$error	.= 'شماره‌ای که باید شارژ شود را وارد نکرده‌اید.‌<br />';
					}
					elseif(!preg_match("/^09([0-9]{9})$/", $data['topup_mobile']))
					{
						$error .= 'شماره وارد شده برای شارژ معتبر نیست.<br>';
					}
					elseif($product['product_product_id'] == 22 AND substr($data['topup_mobile'], 0, 4) != '0941')
					{
						$error .= 'شماره وارد شده متعلق به وایمکس ایرانسل نمی‌باشد.<br>';
					}
					elseif(($product['product_product_id'] == 20 OR $product['product_product_id'] == 21 OR $product['product_product_id'] == 23) AND !in_array(substr($data['topup_mobile'], 0, 4), array('0901','0902','0930','0933','0935','0936','0937','0938','0939')))
					{
						$error .= 'شماره وارد شده متعلق به ایرانسل نمی‌باشد.<br>';
					}
					elseif($product['product_product_id'] >= 24 AND $product['product_product_id'] <= 28 AND substr($data['topup_mobile'], 0, 3) != '091')
					{
						$error .= 'شماره وارد شده متعلق به همراه اول نمی‌باشد.<br>';
					}
					
					if($product['product_product_id'] >= 20 AND $product['product_product_id'] <= 23)
					{
						if(!$data['topup_amount'])
						{
							$error	.= 'مبلغ شارژ را وارد نکرده‌اید.‌<br />';
						}
						elseif (filter_var($data['topup_amount'], FILTER_VALIDATE_INT)== false)
						{
							$error 	.= 'مبلغ شارژ فقط باید شامل اعداد باشد.<br />';
						}
						elseif($data['topup_amount'] < 10000 OR $data['topup_amount'] > 2500000)
						{
							$error .= 'مبلغ شارژ باید بین ۱۰.۰۰۰ تا ۲.۵۰۰.۰۰۰ ریال باشد.<br>';
						}
						elseif ($data['topup_amount'] % 1000 != 0)
						{
							$error .= 'مبلغ شارژ باید مضربی از ۱۰۰۰ باشد.<br>';
						}
						else
						{
							$amount_error = 0;
						}
					}
				}
				
				if($amount_error == 0 AND $product['product_product_id'] >= 20 AND $product['product_product_id'] <= 23 )
				{
					$amount = $data['topup_amount'];
					
					if($data['topup_amount'] > $balance)
					{
						$error .= 'در حال حاضر امکان ارایه محصول مورد نظر وجود ندارد.‌<br />';
					}
					
				}
				else
				{
					$amount = $db->retrieve('product_price','product','product_id',$data['card'])*$data['qty'];
					if($amount > $balance)
					{
						$error .= 'در حال حاضر امکان ارایه محصول مورد نظر وجود ندارد.‌<br />';
					}
				}
			}
			else
			{
				$count_query	= 'SELECT COUNT(*) FROM `card` WHERE `card_product` = "'.$data['card'].'" AND (`card_res_time` < "'.($now-(60*$config['card']['reserveExpire'])).'" OR `card_res_time` = "") AND `card_status` = "1" AND `card_show` = "1"';
				$count_card		= $db->fetch($count_query);
				$total_card		= $count_card['COUNT(*)'];
				if ($total_card < $data['qty'])
					if ($total_card != 0)
						$error .= 'متاسفانه تعداد کارت درخواستی شما در حال حاضر موجود نمی‌باشد٬ شما الان می‌توانید حداکثر '.Convertnumber2farsi($total_card).' کارت از این نوع سفارش دهید.<br />';
					else
						$error .= 'متاسفانه کارت درخواستی شما در حال حاضر موجود نمی‌باشد.‌<br />';
				$amount = $db->retrieve('product_price','product','product_id',$data['card'])*$data['qty'];
			}
		}
		if (!$data['gateway'])
			$error	.= 'دروازه پرداخت را مشخص نکرده اید.‌<br />';
		
		$input_validate	= $db->retrieve('config_input_validate','config','config_id',1);
		if ($input_validate)
		{
			if (!$data['email'] AND !$data['mobile'])
				$error	.= 'برای استفاده از پشتیبانی سایت ایمیل یا شماره همراه خود را وارد کنید.‌<br />';
			if ($data['email'] AND filter_var($data['email'], FILTER_VALIDATE_EMAIL)== false)
				$error .= 'ایمیل وارد شده نامعتبر است.<br />';
			if ($data['mobile'] AND !preg_match("/^09([0-9]{9})$/", $data['mobile']))
				$error .= "شماره همراه نامعتبر است.<br />";
		}
		if($error)
			echo $error.'__2';
		else
		{
			$insert['payment_email']	= $data['email'];
			$insert['payment_mobile']	= $data['mobile'];
			$insert['payment_amount']	= $amount;
			$insert['payment_product']	= $data['card'];
			$insert['payment_qty']		= $data['qty'];
			$insert['payment_gateway']	= $data['gateway'];
			$insert['payment_time']		= $now;
			$insert['payment_ip']		= $server['REMOTE_ADDR'];
			
			$sql 						= $db->queryInsert('payment', $insert);
			$db->execute($sql);
			$payment_id 				= mysql_insert_id();
			
			$randlen					= 9-strlen($payment_id);
			$update['payment_rand']		= $payment_id.get_rand_id($randlen);
			$sql = $db->queryUpdate('payment', $update, 'WHERE `payment_id` = "'.$payment_id.'" LIMIT 1;');
			$db->execute($sql);
			$random						= $update['payment_rand'];
			unset($update);
			
			if($product['product_provider'] == 'db' OR $product['product_provider'] == '')
			{
				$update['card_customer_email']	= $data['email'];
				$update['card_customer_mobile']	= $data['mobile'];
				$update['card_res_user']			= $request['PHPSESSID'];
				$update['card_res_time']			= $now;
				$update['card_payment_id']		= $payment_id;
				$sql = $db->queryUpdate('card', $update, 'WHERE `card_product` = "'.$data['card'].'" AND (`card_res_time` < "'.($now-(60*$config['card']['reserveExpire'])).'" OR `card_res_time` = "") AND `card_status` = "1" AND `card_show` = "1" LIMIT '.$data['qty'].';');
				$db->execute($sql);
			}
			
			echo 'gateway.php?random='.$random.'__1';
		}
		exit;
	}

	$query		= 'SELECT * FROM `category` WHERE `category_parent_id` = "0" ORDER BY `category_order`';
	$categories	= $db->fetchAll($query);
	if ($categories)
		foreach ($categories as $key => $category)
		{
			if ($categories[$key]['category_image'])
				$categories[$key]['category_image'] = $config['MainInfo']['url'].$config['MainInfo']['upload']['image'].'resized/category_'.$category['category_image'];
			$query		= 'SELECT * FROM `product` WHERE `product_category` = "'.$category['category_id'].'" ORDER BY `product_id` ASC';
			$categories[$key]['products']	= $db->fetchAll($query);
			if ($categories[$key]['products'])
				foreach ($categories[$key]['products'] as $product_key => $product)
				{
					$count_query	= 'SELECT COUNT(*) FROM `card` WHERE `card_product` = "'.$product['product_id'].'" AND (`card_res_time` < "'.($now-(60*$config['card']['reserveExpire'])).'" OR `card_res_time` = "") AND `card_status` = "1" AND `card_show` = "1"';
					$count_card		= $db->fetch($count_query);
					$total_card		= $count_card['COUNT(*)'];
					$categories[$key]['products'][$product_key]['counter'] = $total_card;
				}
		}

	$query				= 'SELECT * FROM `plugin` WHERE `plugin_type` = "payment" AND `plugin_status` = "1"';
	$payment_methods	= $db->fetchAll($query);

	$banks_logo = '';
	for ($i=0;$i<768;$i=$i+32)	{
		$banks_logo 	.= '<li style="background-position: -'.$i.'px 0px;"></li>';
	}

	//-- نمایش صفحه
	$query	= 'SELECT * FROM `config` WHERE `config_id` = "1" LIMIT 1';
	$config	= $db->fetch($query);

	$smarty->assign('config', $config);
	$smarty->assign('categories', $categories);
	$smarty->assign('payment_methods', $payment_methods);
	$smarty->assign('banks_logo', $banks_logo);
	$smarty->display('index.tpl');
	exit;
