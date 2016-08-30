<?
/*
  Virtual Freer
  http://freer.ir/virtual

  Copyright (c) 2011 Mohammad Hossein Beyram, freer.ir

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v3 (http://www.gnu.org/licenses/gpl-3.0.html)
  as published by the Free Software Foundation.
*/
	global $error,$request,$success;
	if ($error)
	{
?>
	<div class="div-info-error dir"><?=$error?></div>
<?
	}
	elseif ($success)
	{
?>
	<div class="div-info-success dir"><?=$success?></div>
<?
	}
	elseif ($request[add] == 1)
	{
?>
	<div class="div-info-success dir">با موفقیت اضافه شد.</div>
<?
	}
	elseif ($request[edit] == 1)
	{
?>
	<div class="div-info-success dir">با موفقیت ویرایش شد.</div>
<?
	}
	elseif ($request[save] == 1)
	{
?>
	<div class="div-info-success dir">با موفقیت ذخیره شد.</div>
<?
	}
	elseif ($request[delete] == 1)
	{
?>
	<div class="div-info-success dir">عملیات حذف با موفقیت انجام شد.</div>
<?
	}
	elseif ($request[send] == 1)
	{
?>
	<div class="div-info-success dir">خبرنامه با موفقیت ارسال شد.</div>
<?
	}