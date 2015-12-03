{template index/header}

<div class="banner"><img src="{ROOT_IMAGE}/other/banner-shop.png"></div>

<ul class="menub">
	<li class="border-left-none"><a href="?index=shop&type=1">精灵球</a>
	<li><a href="?index=shop&type=4">药物</a>
</ul>

<div class="flt-r" style="padding: 10px 20px 10px 20px;">您共有 <span id="currency">$trainer[currency]</span> $system[currency_name]</div>

<br clear="both">

<table class="shop-list">
	<tr class="hd"><td width="10%">图片</td><td width="10%">名称</td><td width="30%">介绍</td><td width="10%">价格</td><td width="10%">库存</td><td width="10%">月销售</td><td width="20%"></td></tr>
	<!--{loop $item $val}-->
		<tr id="i$val[item_id]" class="item"><td><div style="background-image:url($val[itemimgpath])"></div></td><td>$val[name]</td><td>$val[description]</td><td>$val[price]</td><td>$val[stock]</td><td>$val[month_sale] ($val[mthsellper]%)</td><td><button data-itemid="$val[item_id]" data-name="$val[name]">购买</button></td></tr>
	<!--{/loop}-->
</table>

{template index/footer}