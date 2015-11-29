<?php

//1分钟5经验，需要付的货币为(向下取整(小时 / 6) + 1) * 5，递增。
/**
 * Place
 * 1-6:身上
 * 7-饲养院
 * 8-PC恢复
 * 9-丢弃
 * 10-交换
 * 101-200:箱子
 *
 * 想法
 * - 自由随机交配
 * - 和固定朋友交配
 * - 只允许有三个配偶，配偶不准是自己的兄弟姐妹爸爸妈妈以及更老的一代（在蛋生出来后才确定）
 * - 情人节派发V蛋
 * - 圣诞节派发C蛋
 * - 万圣节派发H蛋
 */

Kit::Library('class', array('obtain'));
 
# Note that perhaps I will add Exp adding progress

/*
	First extract data from the database, dayctime which records the time the pokemon had sent into daycare, modifies when take the pokemon out
	eggcheck records the timestamp of last time being checked if there is an egg or not, only modify when starts to check is it any eggs produced
*/

$query		= DB::query('SELECT m.pid, m.level, m.nickname, m.id, m.dayctime, m.eggcheck, m.egg, m.gender, m.originuid, m.imgname, m.crritem, m.capitem, p.egggrp, p.egggrpb, p.name FROM pkm_mypkm m LEFT JOIN pkm_pkmdata p ON m.id = p.id WHERE place = 7 AND uid = ' . $_G['uid'] . ' LIMIT 2');
$pokemon	= array();

while($info = DB::fetch($query)) {

	$info['incexp']			= floor(($_SERVER['REQUEST_TIME'] - $info['dayctime']) / 12);
	$info['cost']			= (floor(($_SERVER['REQUEST_TIME'] - $info['dayctime']) / 2400) + 1) * 5;
	$info['egggrpn']		= Obtain::EggGroupName($info['egggrp'], $info['egggrpb']);
	$info['pkmimgpath']		= Obtain::Sprite('pokemon', 'png', $info['imgname']);
	$info['gendersign']		= Obtain::GenderSign($info['gender']);
	$info['capitem']		= Obtain::Sprite('item', 'png', 'item_' . $info['capitem']);
	$info['itemimgpath']	= ($info['crritem']) ? Obtain::Sprite('item', 'png', 'item_' . $info['crritem']) : '';

	$pokemon[] 			= $info;

}

$pmcount = count($pokemon);

if($pmcount === 2) {

	$randmax = 0;

	/*
			Egg out limitation processes (By order)
			- NOT in 'undiscovered' group
				- Pokemon are Ditto and others
				- Different gender
					- Egg groups between two pokemon are match
			If one of the above happend, variable $eggpossible become true, 
			so that pokemon will get a chance of getting egg
		*/

	$eggpossible = 0;

	if(!in_array(15, array($pokemon[0]['egggrp'], $pokemon[1]['egggrp']))) {

		if(in_array(132, array($pokemon[0]['id'], $pokemon[1]['id']))) {

			$eggpossible = 1;

		} elseif($pokemon[0]['gender'] != $pokemon[1]['gender'] && !in_array(0, array($pokemon[0]['gender'], $pokemon[1]['gender']))) {

			if(in_array($pokemon[0]['egggrp'], array($pokemon[1]['egggrp'], $pokemon[1]['egggrpb'])) || !empty($pokemon[0]['egggrpb']) && in_array($pokemon[0]['egggrpb'], array($pokemon[1]['egggrp'], $pokemon[1]['egggrpb'])))
			
				$eggpossible = 1;
				
		}
		
	}

	if($eggpossible === 1) {

		if($pokemon[0]['id'] === $pokemon[1]['id']) {

			if($pokemon[0]['originuid'] === $pokemon[1]['originuid']) {

				$randmax	= 50;
				$psbstatus	= '两只精灵的感情还行。';

			} elseif($pokemon[0]['originuid'] != $pokemon[1]['originuid']) {

				$randmax	= 70;
				$psbstatus	= '两只精灵的感情不错啊！';

			}

		} else {

			if($pokemon[0]['originuid'] === $pokemon[1]['originuid']) {

				$randmax	= 20;
				$psbstatus	= '两只精灵的感情勉强说得过去吧……';

			} elseif($pokemon[0]['originuid'] != $pokemon[1]['originuid']) {

				$randmax	= 50;
				$psbstatus	= '两只精灵的感情还行。';

			}
		
		}
	}

	if($pokemon[0]['egg'] + $pokemon[1]['egg'] === 2) {
	
		$randmax	= 70;
		$psbstatus	= '这一对异性恋进行了一番巫山云雨，最终产下了悲剧的结晶！';

	} else {
		
		// If $eggpossible is 1, then start to get the relationship status, at the same time, define the max number for the random number
		
		if($eggpossible === 1) {
			
			/*
				If two pokemon haven't got an egg, add a record for the egg which is produced
				It is enough of using one of theirs eggcheck and dayctime
				$chktime is a variable which define as the loop times for using $randmax to check if any egg is coming
				$chktimen is the leftover time for time used in loops, then subtracted with the timestamp for next time checking
				$hour is how many hours do a check
				Do loops for $chktime times, each time generates a random number between 0 and 100, 
				if the number is less or equal to the limitation variable $randmax, so an egg has been produced
			*/
			
			# Note that it might be better to create another table of saving records of eggs rather than merge with the table pkm_mypkm

			if(empty($pokemon[0]['egg']) || empty($pokemon[1]['egg'])) {

				$hour		= 2;				// two hours
				$stamp		= $hour * 60 * 60;	// change hours into seconds
				$chktime	= !empty($pokemon[0]['eggcheck']) ? floor(($_SERVER['REQUEST_TIME'] - $pokemon[0]['eggcheck']) / $stamp) : floor(($_SERVER['REQUEST_TIME'] - $pokemon[0]['dayctime']) / $stamp); //每两小时加一次循环，计算循环次数，一次性计算是否生出了蛋，计算完毕后马上更新检查时间

				if($chktime > 0) {

					$chktimen = $_SERVER['REQUEST_TIME']; // Wait for further consideration. $chktimen = $_SERVER['REQUEST_TIME'] - fmod(($_SERVER['REQUEST_TIME'] - $pokemon[0]['eggcheck']), $stamp);

					DB::query('UPDATE pkm_mypkm SET eggcheck = ' . $chktimen . ' WHERE pid IN (' . $pokemon[0]['pid'] . ', ' . $pokemon[1]['pid'] . ')');

				}

				for($i = 0; $i < $chktime; $i++) {

					if(rand(0, 100) <= $randmax) {

						$pokemon[0]['egg'] = $pokemon[1]['egg'] = 1;

						DB::query('UPDATE pkm_mypkm SET egg = 1 WHERE pid IN (' . $pokemon[0]['pid'] . ', ' . $pokemon[1]['pid'] . ')');

						break;

					}
					
				}

			}

		} elseif($eggpossible === 0) {
		
			$psbstatus = '嘛、两只精灵似乎做朋友更合适点？';
			
		}

	}

	$egg	= ($pokemon[0]['egg'] + $pokemon[1]['egg'] === 2) ? 1 : 0;
	$status	= '精灵一切安好。';
	
	if($egg === 1) {
	
		$eggsprite	= Obtain::Sprite('egg', 'png', '');
		
	}


} elseif($pmcount === 1) {

	$status = '精灵一切安好。';
	
}


// If the spot of daycare is not full, get data of pokemon in party and display

if($pmcount < 2) {

	$pokemon = array_merge($pokemon, array_fill($pmcount, 2 - $pmcount, array()));

	$query	= DB::query('SELECT m.id, m.nickname, m.pid, m.imgname, m.level, m.gender, p.egggrp, p.egggrpb, p.name FROM pkm_mypkm m LEFT JOIN pkm_pkmdata p ON m.id = p.id AND m.id != 0 WHERE place IN (1, 2, 3, 4, 5, 6) AND uid = ' . $_G['uid'] . ' LIMIT 6');
	$party	= array();

	while($info = DB::fetch($query)) {

		$info['egggrp']		= Obtain::EggGroupName($info['egggrp'], $info['egggrpb']);
		$info['pkmimgpath']	= Obtain::Sprite('pokemon', 'png', $info['imgname']);
		$info['gender']		= Obtain::GenderSign($info['gender']);
	
		$party[] = $info;

	}

}

?>