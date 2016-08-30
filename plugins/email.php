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
	$pluginData[email][type] = 'notify';
	$pluginData[email][name] = 'ایمیل محصول';
	$pluginData[email][uniq] = 'email';
	$pluginData[email][description] = 'ارسال اطلاعات خرید به ایمیل کاربر';
	$pluginData[email][author][name] = 'Freer';
	$pluginData[email][author][url] = 'http://freer.ir';
	$pluginData[email][author][email] = 'hossin@gmail.com';
	
	//-- فیلدهای تنظیمات پلاگین
	$pluginData[email][field][config][1][title] = 'آدرس ایمیل فرستنده';
	$pluginData[email][field][config][1][name] = 'email';
	$pluginData[email][field][config][2][title] = 'نام فرستنده';
	$pluginData[email][field][config][2][name] = 'name';
	$pluginData[email][field][config][3][title] = 'عنوان ایمیل';
	$pluginData[email][field][config][3][name] = 'title';
	$pluginData[email][field][config][4][title] = 'امضاء';
	$pluginData[email][field][config][4][name] = 'signature';
	
	//-- تابع پردازش و ارسال اطلاعات
	function notify__email($data,$output,$payment,$product,$cards)
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
				 	 '</table><div style="margin-left:auto; margin-right:auto; width:90%; padding:15px 0 0">'.nl2br($product[product_body]).'<br /><center>شناسه پرداخت۱: <b>'.$output[res_num].'</b><br />شناسه پرداخت۲: <b>'.$output[ref_num].'</b>{/if}</center></div>';
			send_mail($data[email],$data[name],$payment[payment_email],$payment[payment_email],$data[title],$table,$data[signature]);
			return true;
		}
		else
		{
			return false;
		}
	}