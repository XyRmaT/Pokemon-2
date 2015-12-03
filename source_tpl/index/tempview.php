{template index/header}

<table class="tbl">
	<legend>共计{$count}只精灵</legend>
	<tr><td>PID</td><td>编号</td><td>精灵</td><td>等级</td><td>经验</td><td>主人</td><td>位置</td><td>个体值</td><td>特性</td><td>性格</td><td>技能</td></tr>
	<!--{loop $pokemon $val}-->
		<tr><td>$val[pkm_id]</td><td>$val[nat_id]</td><td>$val[name] $val[gender]</td><td>$val[level]</td><td>$val[exp]</td><td>$val[uid] $val[username]</td><td>$val[location]</td><td>$val[ind_value]</td><td>$val[ability]</td><td>$val[nature]</td><td>$val[moves]</td></tr>
	<!--{/loop}-->
</table>
{template index/footer}