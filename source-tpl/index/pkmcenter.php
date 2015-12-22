<!--{if INAJAX === FALSE}-->

	{template index/header}

	<div class="banner"><img src="{ROOT_IMAGE}/other/banner-pc.png"></div>

	<div class="pc-nav">
		<div class="sec border-top-none"><div class="ico"></div><a href="?index=pc">精灵治疗</a></div>
		<div class="sec"><div class="ico" style="background-position: 0 -43px"></div><a href="?index=pc&section=box">精灵存取</a></div>
		<div class="sec"><div class="ico" style="background-position: 0 -86px"></div><a href="?index=pc&section=trade">精灵交换</a></div>
	</div>

	<!--{if $_GET['section'] === 'box'}-->

		<div id="pc-boxctnr">
			<!--{loop $pokemon $key $val}-->
				<!--{eval if($key === 'party') continue;}-->
				<div class="pc-box" data-bid="$key" data-limit="$system[perbox]">
					<div class="hd">BOX {echo $key - 100}</div>
					<ul>
						<!--{loop $val $valb}-->
							<li data-pkm_id="$valb[pkm_id]" title="{if empty($valb['nat_id'])}{$valb[nickname]}{else}编号：No.{$valb[nat_id]}<br>精灵：{$valb[name]} {$valb[gender]}<br>昵称：{$valb[nickname]}<br>等级：{$valb[level]}<br>属性：{$valb[type]}<br>特性：{$valb[ability]}{/if}"><img src="./source/plugin/pokemon_n/source-img/pokemon-icon/{$valb[nat_id]}.png"></li>
						<!--{/loop}-->
					</ul>
				</div>
			<!--{/loop}-->
		</div>
		<div id="pc-party" class="pc-box" data-bid="1" data-limit="6">
			<div class="hd">身上</div>
			<ul>
				<!--{loop $pokemon['party'] $val}-->
					<li data-pkm_id="$val[pkm_id]" title="{if empty($val['nat_id'])}{$val[nickname]}{else}编号：No.{$val[nat_id]}<br>精灵：{$val[name]} {$val[gender]}<br>昵称：{$val[nickname]}<br>等级：{$val[level]}<br>属性：{$val[type]}<br>特性：{$val[ability]}{/if}"><img src="./source/plugin/pokemon_n/source-img/pokemon-icon/{$val[nat_id]}.png"></li>
				<!--{/loop}-->
			</ul>
		</div>
		<button id="pc-boxsave">保存改动</button>
	<!--{elseif $_GET['section'] === 'trade'}-->

		<form id="pc-trade" onsubmit="return false;">
		
			<div class="pc-tradenav"><a href="?index=pc&section=trade">交换请求</a></div><div class="pc-tradenav" style="margin-left:20px"><a href="?index=pc&section=trade&part=search">交换大厅</a></div>
			
			<br clear="both">
			
			<!--{if $_GET['part'] === 'search'}-->
			
				<label for="cdtn-username">搜索训练师</label> <input type="text" id="cdtn-username" name="cdtn-username"> &nbsp;&nbsp;&nbsp;
				<label for="cdtn-pokemon">搜索精灵</label> <input type="text" id="cdtn-pokemon" name="cdtn-pokemon"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<button class="sub-search">搜索</button>
				
				<br clear="both">
				
				<div id="res"></div>
				
			<!--{else}-->
				<h3>发送的请求</h3>
				<hr>
				<!--{if !empty($sent)}-->
					<ul class="pc-tradelist">
						<!--{loop $sent $val}-->
							<li data-pkm_id="$val[pkm_id]">
								<div>本方精灵</div>
								<div class="lbd">请求精灵</div>
								<div><img src="$val[pkmimgpath]" title="<!--{if $val['nat_id']}-->No.$val[nat_id] $val[name] $val[gender]<br>等级：$val[level]<br>昵称：$val[nickname]<br>属性：$val[type]<br>性格：$val[nature]<!--{else}-->$val[nickname]<!--{/if}-->"></div>
								<div class="lbd"><img src="$val[opkmimgpath]" title="<!--{if $val['oid']}-->No.$val[oid] $val[oname] $val[ogender]<br>等级：$val[olevel]<br>昵称：$val[onickname]<br>属性：$val[otype]<br>性格：$val[onature]<!--{else}-->$val[onickname]<!--{/if}-->"></div>
								<br clear="both"><br>
								请求日期：$val[time]<br>
								<button class="sub-cancel">取消</button>
							</li>
						<!--{/loop}-->
					</ul>
				<!--{/if}-->
				<div class="no<!--{if !empty($sent)}--> h<!--{/if}-->">没有等待回复的请求。</div>
				
				<p class="cl">
				
				<h3>接受的请求</h3>
				<hr>
				<!--{if !empty($received)}-->
					<ul class="pc-tradelist">
						<!--{loop $received $val}-->
							<li data-pkm_id="$val[pkm_id]">
								<div>对方精灵</div>
								<div class="lbd">请求精灵</div>
								<div><img src="$val[pkmimgpath]" title="<!--{if $val['nat_id']}-->No.$val[nat_id] $val[name] $val[gender]<br>等级：$val[level]<br>昵称：$val[nickname]<br>属性：$val[type]<br>性格：$val[nature]<!--{else}-->$val[nickname]<!--{/if}-->"></div>
								<div class="lbd"><img src="$val[opkmimgpath]" title="<!--{if $val['oid']}-->No.$val[oid] $val[oname] $val[ogender]<br>等级：$val[olevel]<br>昵称：$val[onickname]<br>属性：$val[otype]<br>性格：$val[onature]<!--{else}-->$val[onickname]<!--{/if}-->"></div>
								<br clear="both"><br>
								请求日期：$val[time]<br>
								<button class="sub-accept">同意</button> <button class="sub-decline">拒绝</button>
							</li>
						<!--{/loop}-->
					</ul>
				<!--{/if}-->
				<div class="no<!--{if !empty($received)}--> h<!--{/if}-->">暂时没有交换请求。</div>
			<!--{/if}-->
			
		</form>

	<!--{else}-->
		<form id="pc-heal" action="?index=pc&process=pcheal" onsubmit="return false;">

			<span class="pc-sec" style="margin-left: 15px; ">请选择要治疗/取出的精灵</span>
			<span class="flt-r pc-sec">治疗<em id="pmcount">0</em>只精灵 <button disabled="disabled">治疗&取出</button></span>

			<ul class="pc-info">

				<!--{loop $pokemon $key $val}-->
                    <!--{if !empty($val['pkm_id'])}-->
                        <li class="heal{if $key === 0} lmg-clr{/if}">
                            <div class="txt-c">
                                <img src="$val[pkmimgpath]"><br>
                                $val[nickname]{$val[gender]} Lv.$val[level]<br>
                            </div>
                            <div class="bar" title="$val[hp]/$val[maxhp]">HP<div class="ctn"><div class="hp" style="width:$val[hpper]%"></div></div></div>
                            <div class="bar" title="$val[exp]/$val[maxexp]">EXP<div class="ctn"><div class="exp" style="width:$val[expper]%"></div></div></div>
                            <input type="checkbox" name="heal[]" value="$val[pkm_id]">
                        </li>
                    <!--{/if}-->
				<!--{/loop}-->
				<br clear="both">
				<!--{loop $heal $key $val}-->
                    <!--{if !empty($val['pkm_id'])}-->
                        <li class="txt-c take{if $key === 0} lmg-clr{/if}">
                            <img src="$val[pkmimgpath]"><br>
                            $val[nickname]{$val[gender]} Lv.$val[level]<br>
                            <!--{if $val['fullheal'] === TRUE}-->已恢复<!--{else}-->恢复需要{$val[hltime][0]}时{$val[hltime][1]}分<!--{/if}-->
                            <input type="checkbox" name="take[]" value="$val[pkm_id]">
                        </li>
                    <!--{/if}-->
				<!--{/loop}-->
				
			</ul>
			
		</form>
	<!--{/if}-->

	{template index/footer}
	
<!--{else}-->
	<!--{if $_GET['section'] === 'trade' && $_GET['part'] === 'search'}-->
		<em class="float-left">目标共有<span class="hl">{$count}</span>只精灵</em> $multi[display]
		<br clear="both">
		<!--{if !empty($pokemon)}-->
			<ul class="pmlist">
				<!--{loop $pokemon $val}-->
					<li data-pkm_id="$val[pkm_id]">
						<img src="$val[pkmimgpath]" title="<!--{if $val['nat_id']}-->No.$val[nat_id] $val[name] $val[gender]<br>等级：$val[level]<br>昵称：$val[nickname]<br>属性：$val[type]<br>性格：$val[nature]<br>主人：$val[username]<!--{else}-->$val[nickname]<!--{/if}-->"><br>
						<button class="sub-trade"<!--{if empty($party)}--> disabled="true">无法<!--{else}-->><!--{/if}-->交换</button>
					</li>
				<!--{/loop}-->
			</ul>
			<!--{if !empty($party)}-->
				<div id="layer-traderequest" class="h">
					与你身上的哪一只交换？<br>
					<table class="pmchoose">
						<tr>
							<td><div class="ui-icon ui-icon-triangle-1-w arrow" data-direction="left"></div></td>
							<td width="80%">
								<ul class="pmtarget">
									<!--{eval $i = 0;}-->
									<!--{loop $party $key $val}-->
										<li{if $i !== 0} class="h"{/if} data-index="$i" data-pkm_id="$val[pkm_id]">
											<img src="$val[pkmimgpath]"><br>
											$val[nickname]<!--{if !empty($val['nat_id'])}-->$val[gender] Lv.$val[level]<!--{/if}-->
										</li>
										<!--{eval ++$i;}-->
									<!--{/loop}-->
								</ul>
							</td>
							<td><div class="ui-icon ui-icon-triangle-1-e arrow" data-direction="right"></div></td>
						</tr>
					</table>
				</div>
			<!--{/if}-->
		<!--{/if}-->
	<!--{/if}-->
<!--{/if}-->