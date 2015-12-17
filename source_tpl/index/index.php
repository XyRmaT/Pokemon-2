{template index/header}

<div class="idx-l float-left">
	<div class="idx-login cl">
		<span>LOGIN</span>
		<input>
		<input style="margin-bottom: 20px; ">
		<button class="idx-btn login float-left">登入</button>
		<button class="idx-btn signup flt-r">注册</button>
		<br clear="all">
	</div>
	<img class="index-banner t1" src="{ROOT_IMAGE}/other/banner-index-1.jpg">
	<img class="index-banner t1" src="{ROOT_IMAGE}/other/banner-index-2.jpg">
	<img class="index-banner t1" src="{ROOT_IMAGE}/other/banner-index-3.jpg">
	<img class="index-banner t1" src="{ROOT_IMAGE}/other/banner-index-4.jpg">
</div>

<div class="index-right float-left">
	<img class="index-banner t0" src="{ROOT_IMAGE}/other/banner-index-0.jpg">

	<div class="index-inner-left float-left">
		<div>
			<div class="index-title">最新动态<a href="#" target="_blank">社区讨论</a></div>
			<ul class="idx-thread">
				<li><span class="category ann"></span>感动口袋大学城活动投票开始<span class="date">2014-12-23</span>
				<li><span class="category evt"></span>感动口袋吧活动投票开始<span class="date">2014-11-04</span>
				<li><span class="category upd"></span>感动口袋双子星活动投票开始<span class="date">2014-03-12</span>
				<li><span class="category fix"></span>感动口袋社区活动投票开始<span class="date">2014-05-01</span>
				<li><span class="category ann"></span>感动口袋星辰活动投票开始<span class="date">2014-12-23</span>
			</ul>
		</div>
		<div>
			<div class="index-title">训练师榜Top5<a href="pokemon.php?index=ranking" target="_blank">更多榜单</a></div>
			<ul class="idx-rank">
				<!--{loop $topTrainer $key $val}-->
					<li>
						<!--{if $key < 3}--><span class="top p{echo $key + 1}"></span><!--{else}--><span><!--{echo $key + 1}--></span><!--{/if}-->
						<img src="$val[avatar]" class="blk-c"><br>
						$val[username]<br>Lv.$val[level]
					</li>
				<!--{/loop}-->
			</ul>
		</div>
	</div>
	
	<div class="idx-inrr flt-r">
		<img class="index-banner t2" src="{ROOT_IMAGE}/other/banner-index-5.jpg">
		<div class="idx-rand">
			<div class="idx-bar">$randpm[nickname]{$randpm[gender]} <em>Lv.$randpm[level]</em></div>
			<p align="center"><img src="$randpm[pkmimgpath]" height="96" width="96" title="$randpm[nickname]"></p>
			<div class="idx-bar">训练师 <em title="$randpm[username]"><!--{echo Kit::cutstr($randpm['username'], 12, '..')}--></em></div>
		</div>
	</div>
	<br clear="all">
</div>

{template index/footer}