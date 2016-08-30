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
	$pluginData[smtp_email][type] = 'notify';
	$pluginData[smtp_email][name] = 'ایمیل محصول';
	$pluginData[smtp_email][uniq] = 'smtp_email';
	$pluginData[smtp_email][description] = 'ارسال اطلاعات خرید به ایمیل کاربر از طریق smtp';
	$pluginData[smtp_email][author][name] = 'Freer';
	$pluginData[smtp_email][author][url] = 'http://freer.ir';
	$pluginData[smtp_email][author][smtp_email] = 'hossin@gmail.com';
	
	//-- فیلدهای تنظیمات پلاگین
	$pluginData[smtp_email][field][config][1][title] = 'آدرس ایمیل فرستنده';
	$pluginData[smtp_email][field][config][1][name] = 'email';
	$pluginData[smtp_email][field][config][2][title] = 'نام فرستنده';
	$pluginData[smtp_email][field][config][2][name] = 'name';
	$pluginData[smtp_email][field][config][3][title] = 'عنوان ایمیل';
	$pluginData[smtp_email][field][config][3][name] = 'title';
	$pluginData[smtp_email][field][config][4][title] = 'امضاء';
	$pluginData[smtp_email][field][config][4][name] = 'signature';
	$pluginData[smtp_email][field][config][5][title] = 'نام کاربری ایمیل';
	$pluginData[smtp_email][field][config][5][name] = 'username';
	$pluginData[smtp_email][field][config][6][title] = 'کلمه عبور ایمیل';
	$pluginData[smtp_email][field][config][6][name] = 'password';
	$pluginData[smtp_email][field][config][7][title] = 'میزبان ایمیل';
	$pluginData[smtp_email][field][config][7][name] = 'host';
	$pluginData[smtp_email][field][config][8][title] = 'پورت';
	$pluginData[smtp_email][field][config][8][name] = 'port';
	
	//-- تابع پردازش و ارسال اطلاعات
	function notify__smtp_email($data,$output,$payment,$product,$cards)
	{
		global $db,$smarty;
		if ($output[status] == 1 AND $payment[payment_email] AND $cards)
		{
				foreach($cards as $card)
				{
					$td_body .= '<td style="text-align:center; font-family:tahoma; font-size:12px;">'.$product[product_title].'</td>';
					if($product[product_first_field_title])
						$td_body .= '<td style="text-align:center; font-family:tahoma; font-size:12px;">'.$card[card_first_field].'</td>';
					if($product[product_second_field_title])
						$td_body .= '<td style="text-align:center; font-family:tahoma; font-size:12px;">'.$card[card_second_field].'</td>';
					if($product[product_third_field_title])
						$td_body .= '<td style="text-align:center; font-family:tahoma; font-size:12px;">'.$card[card_third_field].'</td>';
					$table_body .= '<tr>'.$td_body.'</tr>';
					unset($td_body);
				}
				$td_body .= '<td width="25%" style="background:#CCCCCC; padding:5px 0; text-align:center; font-family:tahoma; font-size:12px; font-weight:bold;">نوع</td>';
				if($product[product_first_field_title])
					$td_body .= '<td width="25%" style="background:#CCCCCC; text-align:center; font-family:tahoma; font-size:12px; font-weight:bold;">'.$product[product_first_field_title].'</td>';
				if($product[product_second_field_title])
					$td_body .= '<td width="25%" style="background:#CCCCCC; text-align:center; font-family:tahoma; font-size:12px; font-weight:bold;">'.$product[product_second_field_title].'</td>';
				if($product[product_third_field_title])
					$td_body .= '<td width="25%" style="background:#CCCCCC; text-align:center; font-family:tahoma; font-size:12px; font-weight:bold;">'.$product[product_third_field_title].'</td>';
				$table = '<table style="margin-left:auto; margin-right:auto; width:90%;">'.
				 	 '<tr>'.
				 	 $td_body.
				 	 '</tr>'.
				 	 $table_body.
				 	 '</table><div style="margin-left:auto; margin-right:auto; width:90%; padding:15px 0 0">'.nl2br($product[product_body]).'<br /><center>شناسه پرداخت۱: <b>'.$output[res_num].'</b><br />شناسه پرداخت۲: <b>'.$output[ref_num].'</b></center></div>';
					send_mail($data[email],$data[name],$payment[payment_email],$payment[payment_email],$data[title],$table,$data[signature],$data[host],$data[port],$data[username],$data[password]);
			return true;
		}
		else
		{
			return false;
		}
	}
