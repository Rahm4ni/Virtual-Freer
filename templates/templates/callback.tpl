{* smarty *}
{include file='header.tpl' title=$config.config_site_title description=$config.config_site_description}
<h2>کارت(های) خریداری شده</h2>
<div class="cards">
<table width="100%">
<tr>
<td width="25%" class="top">نوع</td>
{if $product.product_first_field_title}
	<td width="25%" class="top">{$product.product_first_field_title}</td>
{/if}
{if $product.product_second_field_title}
	<td width="25%" class="top">{$product.product_second_field_title}</td>
{/if}
{if $product.product_third_field_title}
	<td width="25%" class="top">{$product.product_third_field_title}</td>
{/if}
</tr>
{foreach $cards as $card}
<tr>
	<td>{$product.product_title}</td>
	{if $product.product_first_field_title}
		<td align="center">{$card.card_first_field|nl2br}</td>
	{/if}
	{if $product.product_second_field_title}
		<td align="center">{$card.card_second_field|nl2br}</td>
	{/if}
	{if $product.product_third_field_title}
		<td align="center">{$card.card_third_field|nl2br}</td>
	{/if}
</tr>
{/foreach}
</table>
<div class="box">
<div style="color:#063;">پرداخت با موفقیت انجام شد.</div>
<center>{if $output.res_num}شناسه پرداخت۱: <b>{$output.res_num}</b><br />{/if}{if $output.ref_num}شناسه پرداخت۲: <b>{$output.ref_num}</b>{/if}</center>
{$product.product_body|nl2br}
</div>
</div>
     <div class="cleaner"></div>
{include file='footer.tpl'}