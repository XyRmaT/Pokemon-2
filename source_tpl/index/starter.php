{template index/header}

<div class="st-description">
	欢迎来到口袋妖怪的冒险世界！请选择一只属于你的初始精灵吧！
</div>

<ul class="st-info">
	<!--{loop $pokemon $val}-->
		<li><img src="$val[pkmimgpath]" data-name="$val[name]" data-sid="$val[nat_id]"><br>No.$val[nat_id] $val[name]</li>
	<!--{/loop}-->
</ul>

<div id="lyr-confirm" class="h">
	确定带走<span class="name"></span>么？
</div>

{template index/footer}