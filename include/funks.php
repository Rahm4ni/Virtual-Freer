<?
/*
  Virtual Freer
  http://freer.ir/virtual

  Copyright (c) 2011 Mohammad Hossein Beyram, freer.ir

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v3 (http://www.gnu.org/licenses/gpl-3.0.html)
  as published by the Free Software Foundation.
*/
//---------------------- Clean Input vars Function------------------
function cleaner($input)
{
	if($input)
	{
		if(is_array($input))
		{
			foreach ($input as $key => $value)
			{
				$output[$key] = cleaner($value);
			}
		}
		else
		{
			$output = htmlspecialchars(xss_clean($input),ENT_QUOTES);
		}
		return $output;
	}
	else
	{
		return '';
	}
}

//---------------------- Check Login Function------------------
function check_login()
{
	global $session;
	if ($session[admin] == 1)
		return true;
	else
		return false;
}

//------------------------------- Check if category exist or not
function check_category_exist($id){
	global $db;
	$result = $db->retrieve('category_id','category','category_id',$id);
	if($result)
	{
		return true;
	}
	else
	{
		return false;
	}
}

//------------------------------- Check if product exist or not
function check_product_exist($id){
	global $db;
	$result = $db->retrieve('product_id','product','product_id',$id);
	if($result)
	{
		return true;
	}
	else
	{
		return false;
	}
}

//------------------------------- Check if card exist or not
function check_card_exist($id){
	global $db;
	$result = $db->retrieve('card_id','card','card_id',$id);
	if($result)
	{
		return true;
	}
	else
	{
		return false;
	}
}

//------------------------------- Check if product exist or not
function check_payment_exist($id){
	global $db;
	$result = $db->retrieve('payment_id','payment','payment_id',$id);
	if($result)
	{
		return true;
	}
	else
	{
		return false;
	}
}

//---------------------- Create random chars Function------------------
function get_rand_id($length)
{
  if($length>0) 
  { 
	$rand_id="";
	for($i=1; $i<=$length; $i++)
	{
		mt_srand((double)microtime() * 1000000);
		$num = mt_rand(1,9);
		$rand_id .= $num;
	}
  }
	return $rand_id;
} 

//---------------------- Send mail Function------------------
function send_mail($from_email,$from_name,$to_email,$to_name,$subject,$body,$signature,$host=null,$port='25',$username=null,$password=null,$attachment=null)
{
	if ($to_email AND filter_var($to_email, FILTER_VALIDATE_EMAIL)== true)
	{
		require_once('libs/class.phpmailer.php');
		if ($signature)
			$signature = '
				<tr>
					<td style="background-color:#3a3a3a; padding:5px; direction:rtl; text-align:right; font-size: 10px; font-family:tahoma; color:#E0E0E0">'.$signature.'</td>
				</tr>';
		
		$mail_body = '
			<table style="margin-left:auto; margin-right:auto; width:80%; border:0px;">
				<tr>
					<td style="background-color:#3a3a3a; padding:5px; direction:rtl; text-align:right; font-size: 12px; font-family:tahoma; font-weight:bold; color:#E0E0E0">'.$from_name.'</td>
				</tr>
				<tr>
					<td style="background-color:#f5f5f5; padding:25px; border: 1px solid #c6c6c6; direction:rtl; text-align:right; font-size: 12px; font-family:tahoma; color:#3a3a3a">'.$body.'</td>
				</tr>'.$signature.'
			</table>';
		$mail = new PHPMailer(true); //defaults to using php "mail()"; the true param means it will throw exceptions on errors, which we need to catch
		try {
		  if ($host AND $username AND $password)
		  {
			$mail->IsSMTP(); // enable SMTP
			$mail->SMTPAuth = true;  // authentication enabled
			$mail->Host = $host;
			$mail->Port = $port; 
			$mail->Username = $username;  
			$mail->Password = $password;
		  }
		  $mail->AddReplyTo($from_email, $from_name);
		  $mail->SetFrom($from_email, $from_name);
		  $mail->AddAddress($to_email, $to_name);
		  $mail->CharSet = 'UTF-8';
		  $mail->Subject = $subject;
		  $mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
		  $mail->MsgHTML($mail_body);
		  if ($attachment)
			$mail->AddAttachment($attachment);
		  $mail->Send();
		  return 1;
		} catch (phpmailerException $e) {
		  echo $e->errorMessage(); //Pretty error messages from PHPMailer
		} catch (Exception $e) {
		  echo $e->getMessage(); //Boring error messages from anything else!
		}
	}
}
//---------------------- Get mellat error string Function------------------
function check_mellat_state_error($ResCode)
{
	switch($ResCode){
		case '0' :
			$prompt="تراکنش با موفقيت انجام شد.";
			break;
		case '11' :
			$prompt="شماره کارت نامعتبر است.";
			break;
		case '12' :
			$prompt="موجودي کافي نيست.";
			break;
		case '13' :
			$prompt="رمز نادرست است.";
			break;
		case '14' :
			$prompt="تعداد دفعات وارد کردن رمز پيش از حد مجاز است.";
			break;
		case '15' :
			$prompt="کارت نامعتبراست.";
			break;
		case '17' :
			$prompt="کاربر از انجام تراکنش منصرف شده است.";
			break;
		case '18' :
			$prompt="تاريخ انقضاي کارت گذشته است.";
			break;
		case '111' :
			$prompt="صادرکننده کارت نامعتبر است.";
			break;
		case '112' :
			$prompt="خطاي سوييچ صادرکننده کارت.";
			break;
		case '113' :
			$prompt="پاسخ از صادرکننده کارت دريافت نشد.";
			break;
		case '114' :
			$prompt="دارنده کارت مجاز به انجام اين تراکنش نيست.";
			break;
		case '21' :
			$prompt="پذيرنده نامعتبر است.";
			break;
		case '22' :
			$prompt="ترمينال مجوز ارائه سرويس درخواستي را ندارد.";
			break;
		case '23' :
			$prompt="خطاي امنيتي رخ داده است.";
			break;
		case '24' :
			$prompt="اطلاعات کاربري پذيرنده نامعتبر است.";
			break;
		case '25' :
			$prompt="مبلغ نامعتبر است.";
			break;
		case '31' :
			$prompt="پاسخ نامعتبر است.";
			break;
		case '32' :
			$prompt="فرمت اطلاعات وارد شده صحيح نيست.";
			break;
		case '33' :
			$prompt="حساب نامعتبر است.";
			break;
		case '34' :
			$prompt="خطاي سيستمي.";
			break;
		case '35' :
			$prompt="تاريخ نامعتبر است.";
			break;
		case '41' :
			$prompt="شماره درخواست تکراري است.";
			break;
		case '42' :
			$prompt="تراکنش sale يافت نشد.";
			break;
		case '43' :
			$prompt="قبلا درخواست verify داده شده است.";
			break;
		case '44' :
			$prompt="درخواست verify  يافت نشد.";
			break;
		case '45' :
			$prompt="تراکنش settle شده است.";
			break;
		case '46' :
			$prompt="تراکنش settle نشده است.";
			break;
		case '47' :
			$prompt="تراکنش settle يافت نشد.";
			break;
		case '48' :
			$prompt="تراکنش reverse شده است.";
			break;
		case '49' :
			$prompt="تراکنش refund يافت نشد.";
			break;
		case '412' :
			$prompt="شناسه قبض نادرست است.";
			break;
		case '413' :
			$prompt="شناسه پرداخت نادرست است.";
			break;
		case '414' :
			$prompt="سازمان صادرکننده قبض نامعتبر است.";
			break;
		case '415' :
			$prompt="زمان جلسه کاري به پايان رسيده است.";
			break;
		case '416' :
			$prompt="خطا در ثبت اطلاعات.";
			break;
		case '417' :
			$prompt="شناسه پرداخت کننده نامعتبراست.";
			break;
		case '418' :
			$prompt="اشکال در تعريف اطلاعات مشتري.";
			break;
		case '419' :
			$prompt="تعداد دفعات ورود اطلاعات از حد مجاز گذشته است.";
			break;
		case '421' :
			$prompt="IP نامعتبر است.";
			break;
		case '51' :
			$prompt="تراکنش تکراري است.";
			break;
		case '52' :
			$prompt="سرويس درخواستي موجود نمي باشد.";
			break;
		case '54' :
			$prompt="تراکنش مرجع موجود نيست.";
			break;
		case '55' :
			$prompt="تراکنش نامعتبر است.";
			break;
		case '61' :
			$prompt="خطا در واريز.";
			break;
		DEFAULT :
			$prompt="خطاي نامشخص.";
			break;
	}
	return  'کد ' . $ResCode .' : '. $prompt;
}

//---------------------- xml2array Function------------------
function xml2array($contents, $get_attributes=1, $priority = 'tag') {
    if(!$contents) return array();

    if(!function_exists('xml_parser_create')) {
        //print "'xml_parser_create()' function not found!";
        return array();
    }

    //Get the XML parser of PHP - PHP must have this module for the parser to work
    $parser = xml_parser_create('');
    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); # http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($parser, trim($contents), $xml_values);
    xml_parser_free($parser);

    if(!$xml_values) return;//Hmm...

    //Initializations
    $xml_array = array();
    $parents = array();
    $opened_tags = array();
    $arr = array();

    $current = &$xml_array; //Refference

    //Go through the tags.
    $repeated_tag_index = array();//Multiple tags with same name will be turned into an array
    foreach($xml_values as $data) {
        unset($attributes,$value);//Remove existing values, or there will be trouble

        //This command will extract these variables into the foreach scope
        // tag(string), type(string), level(int), attributes(array).
        extract($data);//We could use the array by itself, but this cooler.

        $result = array();
        $attributes_data = array();
        
        if(isset($value)) {
            if($priority == 'tag') $result = $value;
            else $result['value'] = $value; //Put the value in a assoc array if we are in the 'Attribute' mode
        }

        //Set the attributes too.
        if(isset($attributes) and $get_attributes) {
            foreach($attributes as $attr => $val) {
                if($priority == 'tag') $attributes_data[$attr] = $val;
                else $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
            }
        }

        //See tag status and do the needed.
        if($type == "open") {//The starting of the tag '<tag>'
            $parent[$level-1] = &$current;
            if(!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag
                $current[$tag] = $result;
                if($attributes_data) $current[$tag. '_attr'] = $attributes_data;
                $repeated_tag_index[$tag.'_'.$level] = 1;

                $current = &$current[$tag];

            } else { //There was another element with the same tag name

                if(isset($current[$tag][0])) {//If there is a 0th element it is already an array
                    $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
                    $repeated_tag_index[$tag.'_'.$level]++;
                } else {//This section will make the value an array if multiple tags with the same name appear together
                    $current[$tag] = array($current[$tag],$result);//This will combine the existing item and the new item together to make an array
                    $repeated_tag_index[$tag.'_'.$level] = 2;
                    
                    if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well
                        $current[$tag]['0_attr'] = $current[$tag.'_attr'];
                        unset($current[$tag.'_attr']);
                    }

                }
                $last_item_index = $repeated_tag_index[$tag.'_'.$level]-1;
                $current = &$current[$tag][$last_item_index];
            }

        } elseif($type == "complete") { //Tags that ends in 1 line '<tag />'
            //See if the key is already taken.
            if(!isset($current[$tag])) { //New Key
                $current[$tag] = $result;
                $repeated_tag_index[$tag.'_'.$level] = 1;
                if($priority == 'tag' and $attributes_data) $current[$tag. '_attr'] = $attributes_data;

            } else { //If taken, put all things inside a list(array)
                if(isset($current[$tag][0]) and is_array($current[$tag])) {//If it is already an array...

                    // ...push the new element into that array.
                    $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
                    
                    if($priority == 'tag' and $get_attributes and $attributes_data) {
                        $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
                    }
                    $repeated_tag_index[$tag.'_'.$level]++;

                } else { //If it is not an array...
                    $current[$tag] = array($current[$tag],$result); //...Make it an array using using the existing value and the new value
                    $repeated_tag_index[$tag.'_'.$level] = 1;
                    if($priority == 'tag' and $get_attributes) {
                        if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well
                            
                            $current[$tag]['0_attr'] = $current[$tag.'_attr'];
                            unset($current[$tag.'_attr']);
                        }
                        
                        if($attributes_data) {
                            $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
                        }
                    }
                    $repeated_tag_index[$tag.'_'.$level]++; //0 and 1 index is already taken
                }
            }

        } elseif($type == 'close') { //End of tag '</tag>'
            $current = &$parent[$level-1];
        }
    }
    
    return($xml_array);
}
//---------------------- xss_clean Function------------------
function xss_clean($data)
{
	$data = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $data);
	$data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
	$data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
	$data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

	$data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

	$data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
	$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
	$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

	$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
	$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
	$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

	$data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

	do
	{
			$old_data = $data;
			$data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
	}
	while ($old_data !== $data);

	return $data;
}
