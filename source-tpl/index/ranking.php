{template index/header}

<div class="banner"><img src="{ROOT_IMAGE}/other/banner-ranking.png"></div>

<div class="rank-list" style="margin-left:0">
	<h4>训练师榜</h4>
	<ul>
		<!--{loop $topTrainer $key $val}-->
			<li<!--{if $key === 0}--> class="tbd-cl"<!--{/if}-->><!--{if $key < 3}--><div class="rank-icon" style="background-position:0 {echo $key * -24}px"></div><!--{else}--><div class="rank-icont">{echo $key + 1}</div><!--{/if}--><img class="avt" src="$val[avatar]"><b>$val[trainer_name]</b><br>Lv.$val[level]</li>
		<!--{/loop}-->
	</ul>
</div>
<div class="rank-list rank-pm">
	<h4>精灵等级榜</h4>
	<ul>
		<!--{loop $pokemon $key $val}-->
			<li<!--{if $key === 0}--> class="tbd-cl"<!--{/if}-->><!--{if $key < 3}--><div class="rank-icon" style="background-position:0 {echo $key * -24}px"></div><!--{else}--><div class="rank-icont">{echo $key + 1}</div><!--{/if}--><img src="{ROOT_IMAGE}/pokemon-icon/$val[nat_id].png" title="$val[trainer_name]的{$val[nickname]}"><b>$val[nickname]{$val[gender]}</b><br>Lv.$val[level]</li>
		<!--{/loop}-->
	</ul>
</div>
<div class="rank-list">
	<h4>图鉴登记榜</h4>
	<ul>
		<!--{loop $pokedex $key $val}-->
			<li<!--{if $key === 0}--> class="tbd-cl"<!--{/if}-->><!--{if $key < 3}--><div class="rank-icon" style="background-position:0 {echo $key * -24}px"></div><!--{else}--><div class="rank-icont">{echo $key + 1}</div><!--{/if}--><img class="avt" src="$val[avatar]"><b>$val[trainer_name]</b><br>登记了$val[total]只精灵</li>
		<!--{/loop}-->
	</ul>
</div>
<div class="rank-list">
	<h4>图鉴收服榜</h4>
	<ul>
		<!--{loop $pokedexb $key $val}-->
			<li<!--{if $key === 0}--> class="tbd-cl"<!--{/if}-->><!--{if $key < 3}--><div class="rank-icon" style="background-position:0 {echo $key * -24}px"></div><!--{else}--><div class="rank-icont">{echo $key + 1}</div><!--{/if}--><img class="avt" src="$val[avatar]"><b>$val[trainer_name]</b><br>收服了$val[total]只精灵</li>
		<!--{/loop}-->
	</ul>
</div>

{template index/footer}