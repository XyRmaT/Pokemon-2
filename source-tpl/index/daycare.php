<!--{if !INAJAX}-->

{template index/header}

<div class="banner"><img src="{ROOT_IMAGE}/other/banner-daycare.png"></div>

<!--{/if}-->

<div class="dc-info">

	<div class="dc-description">
		欢迎来到饲育屋！我和老奶奶一起为年轻的训练师们照看精灵，如果你有什么急事又担心精灵的成长那就交给我们吧！<br><br>
		<!--{if !empty($psbstatus)}--><div class="m-$randmax">$psbstatus</div><!--{else}-->$status<!--{/if}-->
	</div>

	<ul class="dc-pm">
		<!--{loop $pokemon $val}-->
			<li>
				<!--{if empty($val)}-->
					<div class="helper">
					
					</div>
					<button>寄放</button>
				<!--{else}-->
					<span class="float-left">$val[nickname]{$val[gendersign]} Lv.$val[level]</span>
					<span class="flt-r"><img src="$val[item_captured]"><!--{if !empty($val['itemimgpath'])}--><img src="$val[itemimgpath]"><!--{/if}--></span><br class="cl">
					<div class="move_id"><img src="$val[pkmimgpath]" data-pkm_id="$val[pkm_id]"><br>已获得 $val[incexp] Exp.<br>取出需花费 $val[cost] $system[currency_name]</div>
				<!--{/if}-->
			</li>
		<!--{/loop}-->
		<!--{if $egg === 1}-->
			<li>
				<span class="float-left">蛋</span><br class="cl">
				<div class="move_id"><img src="$eggsprite" class="egg"><br>{$pokemon[0][nickname]}{$pokemon[0][gendersign]}与{$pokemon[1][nickname]}{$pokemon[1][gendersign]}幸福的结晶！</div>
			</li>
		<!--{/if}-->
	</ul>
	
	<!--Put here for ajax operation return data replacement-->
	<div id="layer-savedaycare" class="h">
		将谁放到饲育屋寄养呢？<br>
		<table class="pmchoose">
			<tr>
				<td><div class="ui-icon ui-icon-triangle-1-w arrow" data-direction="left"></div></td>
				<td width="80%">
					<ul class="pmtarget">
						<!--{eval $i = 0;}-->
						<!--{loop $party $key $val}-->
							<!--{if !empty($val['nat_id'])}-->
								<li{if $i !== 0} class="h"{/if} data-index="$i" data-pkm_id="$val[pkm_id]">
									<img src="$val[pkmimgpath]"><br>
									$val[nickname]{$val[gender]} Lv.$val[level]<br>
									$val[egg_group]
								</li>
								<!--{eval ++$i;}-->
							<!--{/if}-->
						<!--{/loop}-->
					</ul>
				</td>
				<td><div class="ui-icon ui-icon-triangle-1-e arrow" data-direction="right"></div></td>
			</tr>
		</table>
	</div>

<!--{if !INAJAX}-->
</div>

<div id="layer-getback" class="h">你要带回他么？我们可是很舍不得的……</div>
<div id="layer-getegg" class="h">你要领走这个蛋么？</div>

{template index/footer}
<!--{/if}-->