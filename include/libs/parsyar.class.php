<?php
class parsyar
{
	var $user_id = '';
	var $password = '';
	var $url = 'https://parsyar.com/rest/';
	
	function get_balance()
	{
		$resp = file_get_contents($this->url.'balance/username/'.$this->user_id.'/password/'.$this->password.'/format/json');
		$resp = json_decode($resp);
		return $resp->balance;
	}
	
	function buy_pin($verify_code,$product_id,$qty)
	{
		$resp = file_get_contents($this->url.'buy/username/'.$this->user_id.'/password/'.$this->password.'/verify_code/'.$verify_code.'/product/'.$product_id.'/qty/'.$qty.'/format/json');
		$resp = json_decode($resp);
		return $resp;
	}
	
	function buy_topup($verify_code,$product_id,$price,$mobile)
	{
		$resp = file_get_contents($this->url.'buy/username/'.$this->user_id.'/password/'.$this->password.'/verify_code/'.$verify_code.'/product/'.$product_id.'/price/'.$price.'/mobile/'.$mobile.'/format/json');
		$resp = json_decode($resp);
		return $resp;
	}
	
	function follow_up($verify_code)
	{
		$resp = file_get_contents($this->url.'follow_up/username/'.$this->user_id.'/password/'.$this->password.'/verify_code/'.$verify_code.'/format/json');
		$resp = json_decode($resp);
		return $resp;
	}
}
