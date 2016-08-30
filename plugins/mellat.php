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
	$pluginData[mellat][type] = 'payment';
	$pluginData[mellat][name] = 'درگاه بانک ملت';
	$pluginData[mellat][uniq] = 'mellat';
	$pluginData[mellat][description] = 'مخصوص پرداخت با دروازه پرداخت <a href="http://bankmellat.ir">بانک ملت‌</a>';
	$pluginData[mellat][author][name] = 'Freer';
	$pluginData[mellat][author][url] = 'http://freer.ir';
	$pluginData[mellat][author][email] = 'hossin@gmail.com';
	
	//-- فیلدهای تنظیمات پلاگین
	$pluginData[mellat][field][config][1][title] = 'شماره پايانه پذيرنده';
	$pluginData[mellat][field][config][1][name] = 'terminalId';
	$pluginData[mellat][field][config][2][title] = 'نام كاربري پذيرنده';
	$pluginData[mellat][field][config][2][name] = 'userName';
	$pluginData[mellat][field][config][3][title] = 'كلمه عبور پذيرنده';
	$pluginData[mellat][field][config][3][name] = 'userPassword';
	
	//-- تابع انتقال به دروازه پرداخت
	function gateway__mellat($data)
	{
		global $config,$smarty,$db;
		include_once('include/libs/nusoap.php');
		$terminalId		= trim($data[terminalId]);
		$userName		= trim($data[userName]);
		$userPassword	= trim($data[userPassword]);
		$orderId		= $data[invoice_id];
		$amount 		= $data[amount];
		$localDate		= date('Ymd');
		$localTime		= date('Gis');
		$additionalData	= '';
		$callBackUrl	= $data[callback];
		$payerId		= 0;
		//-- تبدیل اطلاعات به آرایه برای ارسال به بانک
		$parameters = array(
			'terminalId' 		=> $terminalId,
			'userName' 			=> $userName,
			'userPassword' 		=> $userPassword,
			'orderId' 			=> $orderId,
			'amount' 			=> $amount,
			'localDate' 		=> $localDate,
			'localTime' 		=> $localTime,
			'additionalData' 	=> $additionalData,
			'callBackUrl' 		=> $callBackUrl,
			'payerId' 			=> $payerId);
		$client = new nusoap_client('https://pgws.bpm.bankmellat.ir/pgwchannel/services/pgw?wsdl');
		$namespace='http://interfaces.core.sw.bps.com/';
		$result 	= $client->call('bpPayRequest', $parameters, $namespace);
		//-- بررسی وجود خطا
		if ($client->fault)
		{
			//-- نمایش خطا
			$data[title] = 'خطای سیستم';
			$data[message] = '<font color="red">در اتصال به درگاه بانک ملت مشکلی به وجود آمد٬ لطفا از درگاه سایر بانک‌ها استفاده نمایید.</font> خطا: <br />خطا در اتصال به بانک ملت<br /><a href="index.php" class="button">بازگشت</a>';
			$smarty->assign('data', $data);
			$smarty->display('message.tpl');
			exit;
		} 
		else
		{
			$err = $client->getError();
			if ($err)
			{
				//-- نمایش خطا
				$data[title] 	= 'خطای سیستم';
				$data[message] 	= '<font color="red">در اتصال به درگاه بانک ملت مشکلی به وجود آمد٬ لطفا از درگاه سایر بانک‌ها استفاده نمایید.</font> خطا: <br /><pre>'.$err.'</pre><br /><a href="index.php" class="button">بازگشت</a>';
				$smarty->assign('data', $data);
				$smarty->display('message.tpl');
				exit;
			} 
			else
			{
				$res 		= explode (',',$result);
				$ResCode 	= $res[0];
				if ($ResCode == "0")
				{
					$update[payment_rand]	= $res[1];
					$sql = $db->queryUpdate('payment', $update, "WHERE `payment_rand` = '$orderId' LIMIT 1;");
					$db->execute($sql);
					
					$smarty->assign('RefId', $res[1]);
					$smarty->display('mellat.tpl');
					exit;
				}
				else
				{
					//-- نمایش خطا
					$data[title] 	= 'خطای سیستم';
					$data[message] 	= '<font color="red">در اتصال به درگاه بانک ملت مشکلی به وجود آمد٬ لطفا از درگاه سایر بانک‌ها استفاده نمایید.</font> خطا: '.$result.'<br /><a href="index.php" class="button">بازگشت</a>';
					$smarty->assign('data', $data);
					$smarty->display('message.tpl');
					exit;
				}
			}
		}
	}
	
	//-- تابع بررسی وضعیت پرداخت
	function callback__mellat($data)
	{
		global $db,$post,$smarty;
		$sql 	= "SELECT * FROM `payment` WHERE `payment_rand` = '$post[RefId]' LIMIT 1;";
		$payment = $db->fetch($sql);
		if ($payment)
			{
				if ($_POST[ResCode] == '0')
				{
					include_once('include/libs/nusoap.php');
					$client = new nusoap_client('https://pgws.bpm.bankmellat.ir/pgwchannel/services/pgw?wsdl');
					$namespace='http://interfaces.core.sw.bps.com/';
					$terminalId		= trim($data[terminalId]);
					$userName		= trim($data[userName]);
					$userPassword	= trim($data[userPassword]);
					// Check for an error ----- bpVerifyRequest
					$err = $client->getError();
					if ($err)
					{
						$output[status] = 0;
						$output[reversal] = 1;
						$output[message] = $err;
					}
					else
					{
					  	$orderId 				= $post[SaleOrderId];
					  	$verifySaleOrderId 		= $post[SaleOrderId];
					  	$verifySaleReferenceId 	= $post[SaleReferenceId];
					  	
						$parameters = array(
							'terminalId' => $terminalId,
							'userName' => $userName,
							'userPassword' => $userPassword,
							'orderId' => $orderId,
							'saleOrderId' => $verifySaleOrderId,
							'saleReferenceId' => $verifySaleReferenceId);

						// Call the SOAP method
						$result = $client->call('bpVerifyRequest', $parameters, $namespace);

						// Check for a fault
						if ($client->fault)
						{
							$output[status] = 0;
							$output[reversal] = 1;
							foreach ($result as $error)
								$output[message] .= $error;
						}
						else
						{
							$err = $client->getError();
							if ($err)
							{
								$output[status] = 0;
								$output[reversal] = 1;
								$output[message] = $err;
							}
							else 
							{
								if($result == 0)
								{
									// Display the result
									// Update Table, Save Verify Status 
									// Note: Successful Verify means complete successful sale was done.
									$inquirySaleOrderId = $post[SaleOrderId];
									$inquirySaleReferenceId = $post[SaleReferenceId];

									// Check for an error
									$err = $client->getError();
									if ($err)
									{
										$output[status] = 0;
										$output[reversal] = 1;
										$output[message] = $err;
									}
									else
									{
										$parameters = array(
											'terminalId' => $terminalId,
											'userName' => $userName,
											'userPassword' => $userPassword,
											'orderId' => $orderId,
											'saleOrderId' => $inquirySaleOrderId,
											'saleReferenceId' => $inquirySaleReferenceId);

										// Call the SOAP method
										$result = $client->call('bpInquiryRequest', $parameters, $namespace);
										if($result == 0)
										{
											// Check for a fault
											if ($client->fault)
											{
												$output[status] = 0;
												$output[reversal] = 1;
												foreach ($result as $error)
													$output[message] .= $error;
											}
											else
											{
												$err = $client->getError();
												if ($err)
												{
													$output[status] = 0;
													$output[reversal] = 1;
													$output[message] = $err;
												}
												else
												{
													$output[status] = 1;
												}// end Display the result
											}// end Check for errors
										}
										else
										{
											$output[status] = 0;
											$output[reversal] = 0;
											if (function_exists('check_mellat_state_error'))
												$output[message] = check_mellat_state_error($result);
											else
												$output[message] = $result;
										}
									}
								}
								else
								{
									$output[status] = 0;
									$output[reversal] = 0;
									if (function_exists('check_mellat_state_error'))
										$output[message] = check_mellat_state_error($result);
									else
										$output[message] = $result;
								}
							}// end Display the result
						}// end Check for errors
					}
				}
				else
				{
					$output[status]	= 0;
					$output[reversal] = 0;
					$output[message] 	= 'شما از انجام تراكنش منصرف شدید.';
					$cancel = 1;
				}
			}
			else
			{
				$output[status]	= 0;
				$output[reversal] = 0;
				$output[message] 	= 'خریدی مربوط به این کد مرجع تراکنش یافت نشد و یا قبلا پرداخت شده است.';
			}
			//-------------- اگر خطایی نبود درخواست واریز وجه داده شود
			if ($output[status] == 1)
			{
				// Update Table, Save Inquiry Status 
				// Note: Successful Inquiry means complete successful sale was done.
				$settleSaleOrderId 		= $post[SaleOrderId];
				$settleSaleReferenceId 	= $post[SaleReferenceId];
				// Check for an error
				$err = $client->getError();
				if ($err) 
				{
					$output[status] = 0;
					$output[message] = $err;
				}
			  	else
			  	{
					$parameters = array(
						'terminalId' => $terminalId,
						'userName' => $userName,
						'userPassword' => $userPassword,
						'orderId' => $orderId,
						'saleOrderId' => $settleSaleOrderId,
						'saleReferenceId' => $settleSaleReferenceId);
					
					// Call the SOAP method
					$result = $client->call('bpSettleRequest', $parameters, $namespace);
					
					if($result == 0)
					{
						// Check for a fault
						if ($client->fault)
						{
							$output[status] = 0;
							foreach ($result as $error)
								$output[message] .= $error;
						}
						else
						{
							$err = $client->getError();
							if ($err)
							{
								$output[reversal] = 1;
								$output[status] = 0;
								$output[message] = $err;
							} 
							else 
							{
								// Update Table, Save Settle Status 
								// Note: Successful Settle means that sale is settled.
								$output[status]		= 1;
								$output[res_num]	= $post[SaleOrderId];
								$output[ref_num]	= $post[SaleReferenceId];
								$output[payment_id]	= $payment[payment_id];
							}// end Display the result
						}// end Check for errors
					}
					else
					{
						$output[status] = 0;
						$output[reversal] = 1;
						if (function_exists('check_mellat_state_error'))
							$output[message] = check_mellat_state_error($result);
						else
							$output[message] = $result;
					}
				}
			}
			//------------- در غیر اینصورت وجه برگشت داده شود
			if ($output[reversal] == 1)
			{
				$orderId 					= $post[SaleOrderId];
			  	$reversalSaleOrderId 		= $post[SaleOrderId];
			  	$reversalSaleReferenceId 	= $post[SaleReferenceId];

				// Check for an error
				$err = $client->getError();
				if ($err)
				{
					$output[status] = 0;
					$output[message] = $err;
				}
				else
				{
					$parameters = array(
						'terminalId' => $terminalId,
						'userName' => $userName,
						'userPassword' => $userPassword,
						'orderId' => $orderId,
						'saleOrderId' => $reversalSaleOrderId,
						'saleReferenceId' => $reversalSaleReferenceId);
					// Call the SOAP method
					$result = $client->call('bpReversalRequest', $parameters, $namespace);
					// Check for a fault
					if ($client->fault)
					{
						$output[status] = 0;
						foreach ($result as $error)
							$output[message] .= $error;
					}
					else
					{
						$err = $client->getError();
						if ($err) {
							// Display the error
							$output[status] = 0;
							$output[message] = $err;
						}
						else
						{
							// Update Table, Save Reversal Status 
							// Note: Successful Reversal means that sale is reversed.
							if($result == 0)
							{
								$output[message] .= '<br />در موقع پرداخت مشکلی به وجود آمد٬ مبلغ پرداخت شده به حساب شما برگشت داده شد.';
							}
							else
							{
								$output[message] .= '<br />در موقع پرداخت مشکلی به وجود آمد.';
							}
						}// end Display the result
					}// end Check for errors
				}
			}
			elseif ($cancel != 1)
			{
				$output[message] .= '<br />در موقع پرداخت مشکلی به وجود آمد.';
			}
			if ($output[status] == 0)
				$output[message] = '<font color="red">'.$output[message].'</font>';
			
		return $output;
	}