<?php

Kit::Library('class', ['obtain']);

// Random pokemon showcase
$count                = DB::result_first('SELECT COUNT(*) FROM pkm_mypkm WHERE nat_id != 0 AND location IN (1, 2, 3, 4, 5, 6)');
$randpm               = DB::fetch_first('SELECT m.nickname, m.level, m.gender, m.sprite_name, m.uid, mb.username FROM pkm_mypkm m LEFT JOIN pre_common_member mb ON m.uid = mb.uid WHERE m.nat_id != 0 AND m.location IN (1, 2, 3, 4, 5, 6) AND m.uid = 8 LIMIT ' . rand(0, $count - 1) . ', 1');
$randpm['pkmimgpath'] = Obtain::Sprite('pokemon', 'png', $randpm['sprite_name']);
$randpm['gender']     = Obtain::GenderSign($randpm['gender']);


// Top 5 trainer ranking
$topTrainer = [];
$query      = DB::query('SELECT t.uid, t.level, t.exp, mb.username FROM pkm_trainerdata t LEFT JOIN pre_common_member mb ON t.uid = mb.uid ORDER BY exp DESC LIMIT 5');

while($info = DB::fetch($query)) {
	$info['avatar'] = Obtain::Avatar($info['uid']);
	$topTrainer[]   = $info;
}


/*
	Recent threads


$thread	= array();
$query	= DB::query('SELECT tid, subject, dateline FROM pre_forum_thread WHERE fid = 101 ORDER BY tid DESC LIMIT 10');

while($tmp = DB::fetch($query)) {

	$tmp['dateline']	= date('m-d', $tmp['dateline']);
	$thread[]			= $tmp;
	
}


<div class="box-hd">公告</div>

	<div class="box-ct idx-ann">
		
		<ul>
			<!--{loop $thread $key $val}-->
				<li{if $key === 0} class="tbd-cl"{/if}>
					<a href="forum.php?mod=viewthread&tid=$val[tid]" target="_blank">$val[subject]</a>
					<em>$val[dateline]</em>
				</li>
			<!--{/loop}-->
		</ul>
		
	</div>
*/