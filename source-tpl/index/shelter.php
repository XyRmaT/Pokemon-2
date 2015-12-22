{template index/header}

<div class="banner"><img src="{ROOT_IMAGE}/other/banner-shelter.png"></div>

<div class="sht-description">
	孤儿院收留了被训练师抛弃的神奇宝贝，如果您愿意收养它们，只需要交纳200$system[currency_name]的手续费就可以领养它们。<p>
	同时这里还寄存着繁殖的鸡蛋，如果感兴趣的话也可以领走。
</div>

<div class="sht-main pm">
	<!--{if empty($pokemon)}-->
		<div style="margin-top:61px" class="no">没有可领养的精灵</div>
	<!--{else}-->
		<!--{loop $pokemon $val}-->
			<img data-pkm_id="$val[pkm_id]" title="No.$val[nat_id] $val[name]" src="$val[pkmimgpath]">
		<!--{/loop}-->
	<!--{/if}-->
</div>

<div class="sht-main egg">
	<!--{if empty($egg)}-->
		<div style="margin-top:-5px" class="no">没有可领养的精灵蛋</div>
	<!--{else}-->
		<!--{loop $egg $val}-->
			<img data-pkm_id="$val[pkm_id]" title="$val[name]" src="$val[pkmimgpath]">
		<!--{/loop}-->
	<!--{/if}-->
</div>

<div id="layer-claim" class="h">您确定要带走它么？</div>


{template index/footer}