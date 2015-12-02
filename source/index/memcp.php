<?php

Kit::Library('class', ['pokemon', 'obtain']);
//error_reporting(E_ALL);
//Pokemon::Generate(rand(1, 649), $trainer['uid'], array('shiny' => 1, 'mtlevel' => 20));
//if($trainer['gm']) Pokemon::Generate(290, 8, array('mtlevel' => 1, 'shiny' => 0));
//if($trainer['gm']) Pokemon::Generate(0, 4122, array('egg' => 's:1', 'mtplace' => 602));
//if($trainer['gm']) DB::query('UPDATE pkm_mypkm SET exp = exp + 166566, hpns = 255');
//if($trainer['gm']) DB::query('INSERT INTO pkm_myitem (iid, num, uid) VALUES (32, 100, 8)');
//DB::query('UPDATE pre_common_member_count SET extcredits7 = 100000');

/*if($trainer['gm']) {
	$move = [[162, 6666, 'STABLE', 6666, 0], [47, 6666, 'SLEEP', 6666, 0], [28, 6666, 'USELESS', 6666, 0]];
	DB::query('UPDATE pkm_mypkm SET move = \'' . serialize($move) . '\' WHERE pid = 1');

}*/

$_GET['section'] = (!empty($_GET['section']) && in_array($_GET['section'], ['pokedex', 'achievement', 'inbox', 'setting', 'inventory'], TRUE)) ? $_GET['section'] : '';

$rank   = DB::result_first('SELECT COUNT(*) FROM pkm_trainerdata WHERE exp > ' . $trainer['exp']) + 1;
$reqexp = Obtain::TrainerRequireExp($trainer['level'] + 1);
$dexclt = DB::result_first('SELECT COUNT(*) FROM pkm_mypokedex WHERE uid = ' . $trainer['uid'] . ' AND own = 1');

switch($_GET['section']) {
	case '':

		/*
			Use for evolve: m.beauty, m.crritem, m.hpns, m.pv, m.abi, m.form
		*/

		$query   = DB::query('SELECT
			m.id, m.pid, m.gender, m.hp, m.exp, m.level, m.nature, m.nickname, m.form, m.ev, m.iv, m.newmove, m.move, m.imgname, m.capitem, m.egghatch, m.mtdate, m.mtlevel, m.mtplace, m.beauty, m.crritem, m.hpns, m.pv, m.form, m.originuid, m.status, a.name abi, 
			p.bs, p.type, p.typeb, p.exptype, p.name, p.evldata, mb.username 
			FROM pkm_mypkm m LEFT JOIN pkm_pkmdata p ON m.id = p.id AND m.id != 0 LEFT JOIN pkm_abilitydata a ON m.abi = a.aid LEFT JOIN pre_common_member mb ON mb.uid = m.originuid WHERE m.place IN (1, 2, 3, 4, 5, 6) AND m.uid = ' . $trainer['uid'] . ' ORDER BY m.place ASC LIMIT 6');
		$pokemon = $movecriteria = [];

		while($info = DB::fetch($query)) {
			switch($info['id']) {
				case '0':

					/**
					 * [Abandoned method comment]
					 * Total hatch seconds for an egg, 1275 was multiplied from 255 (variable times in the egg cycle) and 5 (5 sec each step)
					 * and the part 1275 * (rand(0, 5) + $info['eggcycle'] * 0.666) / 10 is to set a random rate of correcting the taken time.
					 */
					//$info['maturity'] = round((time() - $info['mtdate']) / $info['hatchTime'] * 100, 3);


					if($info['egghatch'] < $info['mtdate']) {
						$info['eggstatus'] = '这是一颗坏蛋...';
						break;
					}

					$info['pkmimgpath'] = Obtain::Sprite('egg', 'png', '');
					$info['maturity']   = min(floor(($_SERVER['REQUEST_TIME'] - $info['mtdate']) / ($info['egghatch'] - $info['mtdate']) * 100), 90) + min(floor($info['exp'] / 100), 5) * 2;
					$info['capitem']    = Obtain::Sprite('item', 'png', 'item_' . $info['capitem']);
					$info['mtplace']    = Obtain::MeetPlace($info['mtplace']);

					if($info['maturity'] >= 0 && $info['maturity'] < 27) $info['eggstatus'] = '毫无动静……';
					elseif($info['maturity'] >= 27 && $info['maturity'] < 51) $info['eggstatus'] = '蛋轻微地摇了摇……';
					elseif($info['maturity'] >= 51 && $info['maturity'] < 93) $info['eggstatus'] = '似乎从蛋里传来了声音……';
					elseif($info['maturity'] >= 93 && $info['maturity'] < 100) $info['eggstatus'] = '蛋快孵化了！';
					elseif($info['maturity'] >= 100) {

						Pokemon::Hatch($info['pid']);

						$info['eggstatus'] = '呀！小蛋蛋要孵化了！';

					}

					$info['maturity'] .= '（' . $info['maturity'] . '%）<br>' . date('Y-m-d H:i:s', $info['egghatch']);

					break;
				default:
					$info['newmove'] = !empty($info['newmove']) ? unserialize($info['newmove']) : [];
					$info['move']    = !empty($info['move']) ? unserialize($info['move']) : [];

					// Exp
					$info = array_merge($info, Obtain::Stat($info['level'], $info['bs'], $info['iv'], $info['ev'], $info['nature'], $info['hp']));

					list($info['maxexp'], $info['exp'], $info['rmexp'], $info['expper']) = Pokemon::Levelup($info);
					Pokemon::$pmtmp = [];
					unset($info['evldata'], $info['exptype']);

					$info['pkmimgpath']  = Obtain::Sprite('pokemon', 'png', $info['imgname']);
					$info['capitem']     = Obtain::Sprite('item', 'png', 'item_' . $info['capitem']);
					$info['itemimgpath'] = ($info['crritem']) ? Obtain::Sprite('item', 'png', 'item_' . $info['crritem']) : '';
					$info['gender']      = Obtain::GenderSign($info['gender']);
					$info['type']        = Obtain::TypeName($info['type'], $info['typeb'], TRUE, ' blk-c');
					$info['mtplace']     = Obtain::MeetPlace($info['mtplace']);
					$info['nature']      = Obtain::NatureName($info['nature']);
					$info['status']      = Obtain::StatusIcon($info['status']);
					$info['mtdate']      = date('Y年m月d日', $info['mtdate']);

					if($info['hpns'] < 50) $info['hpnsstatus'] = '用陌生而又警惕的眼神望着你。';
					elseif($info['hpns'] >= 50 && $info['hpns'] < 90) $info['hpnsstatus'] = '与你的感情还算可以。';
					elseif($info['hpns'] >= 90 && $info['hpns'] < 150) $info['hpnsstatus'] = '渐渐开始缠着你了……！';
					elseif($info['hpns'] >= 150 && $info['hpns'] < 220) $info['hpnsstatus'] = '你们之间的羁绊越来越深了！';
					elseif($info['hpns'] >= 220) $info['hpnsstatus'] = '没有人可以让你们分开了！';

					foreach($info['move'] as $val) $movecriteria[] = $val[0];

					break;
			}

			$pokemon[] = $info;
		}

		if($movecriteria) {

			$query = DB::query('SELECT mid, type, power, class FROM pkm_movedata WHERE mid IN (' . implode(',', $movecriteria) . ')');
			$move  = [];

			while($info = DB::fetch($query))

				$move[$info['mid']] = [$info['type'], $info['power'], Obtain::MoveClassName($info['class']), Obtain::TypeName($info['type'])];

		}

		break;

	case 'setting':

		if($handle = opendir(ROOTTPL)) {

			$i = 1;

			while(FALSE !== ($filename = readdir($handle))) {

				if(strpos('.', $filename) === FALSE) {

					$list .= '<option value="' . $i . (($i == $trainer['style']) ? '" selected="selected"' : '') . '>' . $filename . '</option>';

					++$i;

				}

			}

			closedir($handle);

		}

		break;

	case 'pokedex':

		$seen    = 0;
		$count   = DB::result_first('SELECT COUNT(DISTINCT id) FROM pkm_pkmdata');
		$query   = DB::query('SELECT md.id, md.own, p.name, p.type, p.typeb FROM pkm_mypokedex md LEFT JOIN pkm_pkmdata p ON p.id = md.id WHERE md.uid = ' . $trainer['uid']);
		$pokemon = array_fill(1, $count, ['own' => 'n']);

		while($info = DB::fetch($query)) {

			++$seen;

			$info['type'] = Obtain::TypeName($info['type'], $info['typeb']);

			$pokemon[$info['id']] = $info;

		}

		break;
	case 'achievement':

		$query       = DB::query('SELECT ac.achvid, ac.name, ac.catid, ac.dscptn, mac.dateline FROM pkm_achievementdata ac LEFT JOIN pkm_myachievement mac ON mac.achvid = ac.achvid AND mac.uid = ' . $trainer['uid'] . ' ORDER BY catid ASC, achvid ASC');
		$achievement = [];
		$catarr      = ['未分类', '图鉴登录'];

		while($info = DB::fetch($query)) {

			$info['catid'] = $catarr[$info['catid']];
			$achievement[] = $info;

		}

		break;
	case 'inbox':

		/*
			Making the multipage
		*/

		$count  = DB::result_first('SELECT COUNT(*) FROM pkm_myinbox WHERE receiver = ' . $trainer['uid']);
		$multi  = Kit::MultiPage(8, $count);
		$unread = 0;

		$query   = DB::query('SELECT msgid, title, content, dateline, marked, sender FROM pkm_myinbox WHERE receiver = ' . $trainer['uid'] . ' ORDER BY dateline DESC LIMIT ' . $multi['start'] . ', ' . $multi['limit']);
		$message = [];

		while($info = DB::fetch($query)) {

			$info['dateline'] = date('Y-m-d H:i:s', $info['dateline']);
			$info['avatar']   = Obtain::Avatar($info['sender']);

			$message[] = $info;

			if(!$info['marked']) ++$unread;

		}

		if($unread) {

			DB::query('UPDATE pkm_trainerdata SET newmsg = 0 WHERE uid = ' . $trainer['uid']);
			DB::query('UPDATE pkm_myinbox SET marked = 1, rdateline = ' . $_SERVER['REQUEST_TIME'] . ' WHERE receiver = ' . $trainer['uid'] . ' AND marked = 0');

		}

		break;
	case 'inventory':

		$query   = DB::query('SELECT pid, nickname, id, level, gender, crritem, imgname FROM pkm_mypkm WHERE place < 7 ORDER BY place ASC');
		$pokemon = [];
		$iids    = [];

		while($info = DB::fetch($query)) {

			if($info['crritem']) $iids[] = $info['crritem'];

			$info['pkmimgpath']  = Obtain::Sprite('pokemon', 'png', $info['imgname']);
			$info['pkmimgpathi'] = Obtain::Sprite('pokemon-icon', 'png', 'picon_' . $info['id']);
			$info['itemimgpath'] = $info['crritem'] ? Obtain::Sprite('item', 'png', 'item_' . $info['crritem']) : '';
			$info['gender']      = Obtain::GenderSign($info['gender']);
			$pokemon[]           = $info;

		}


		$type  = (empty($_GET['type']) || $_GET['type'] < 1 && $_GET['type'] > 4) ? 0 : intval($_GET['type']);
		$query = DB::query('SELECT mi.iid, mi.num, i.name, i.dscptn, i.type, i.usable FROM pkm_myitem mi LEFT JOIN pkm_itemdata i ON i.iid = mi.iid WHERE mi.uid = ' . $trainer['uid'] . ' AND mi.num > 0' . ($iids ? ' UNION ALL SELECT iid, 0 num, name_zh, dscptn, type, usable FROM pkm_itemdata WHERE iid IN (' . implode(',', $iids) . ')' : ''));
		$item  = [];
		$types = ['球类', '进化石', '携带道具', '药物'];

		while($info = DB::fetch($query)) {

			$info['itemimgpath'] = Obtain::Sprite('item', 'png', 'item_' . $info['iid']);
			$item[$info['iid']]  = $info;

		}

		$item = json_encode($item);

		break;
}


/*
if($trainer['uid'] == 8) {

	$query = DB::query('SELECT mid, pp, name FROM pkm_movedata');
	$move = $sql = array();
	while($info = DB::fetch($query)) {
		$move[$info['mid']] = $info;
	}
	$query = DB::query('SELECT pid, move FROM pkm_mypkm');
	while($info = DB::fetch($query)) {
		$info['move'] = unserialize($info['move']);
		foreach($info['move'] as $key => $val) {
			$info['move'][$key][1] = $move[$val[0]]['pp'];
			$info['move'][$key][2] = $move[$val[0]]['name'];
			$info['move'][$key][3] = $move[$val[0]]['pp'];
			$info['move'][$key][4] = 0;
		}
		DB::query('UPDATE pkm_mypkm SET move = \'' . serialize($info['move']) . '\' WHERE pid = ' . $info['pid']);
	}
}*/


/*if($trainer['uid'] == 8) {

	//DB::query('DELETE FROM pkm_mypokedex');

	$query = DB::query('SELECT DISTINCT m.id, m.uid, md.own FROM pkm_mypkm m LEFT JOIN pkm_mypokedex md ON md.id = m.id AND md.uid = m.uid ORDER BY m.uid ASC, m.id ASC');
	
	while($info = DB::fetch($query)) {
	
		if(empty($info['own'])) {
		
			$sql[] = '(' . $info['id'] . ', ' . $info['uid'] . ', 1)';
			
		}
		
	}
	
	if(!empty($sql))
	
		DB::query('INSERT INTO pkm_mypokedex (id, uid, own) VALUES ' . implode(',', $sql));
	
}*/
/*
if($trainer['uid'] == 8 && $_GET['aaaa'] === '1') {

	$gift = [
		4318 => [27 => 1, 29 => 1]
	];

	foreach($gift as $key => $val) {
	
		$query = DB::query('SELECT iid, num FROM pkm_myitem WHERE uid = ' . $key);
		$item = [];
		
		while($info = DB::fetch($query)) {
		
			$item[$info['iid']] = $info['num'];
			
		}
		
		foreach($val as $keyb => $valb) {
		
			if(empty($item[$keyb]) || $item[$keyb] < 1) {
			
				DB::query('INSERT INTO pkm_myitem (iid, num, uid) VALUES (' . $keyb . ', ' . $valb . ', ' . $key . ')');
				
			} else {
			
				DB::query('UPDATE pkm_myitem SET num = ' . ($item[$keyb] + $valb) . ' WHERE iid = ' . $keyb . ' AND uid = ' . $key);
				
			}
			
		}
		
	}
	
}*/
/*
if($trainer['uid'] == 8) {

	$query	= DB::query('SELECT pid, position FROM pre_forum_post WHERE tid = 6431 ORDER BY position ASC');
	$i		= 0;
	$post	= $sql = array();

	while($info = DB::fetch($query)) {
	
		$sql[] = '(' . $info['pid'] . ', ' . ++$i . ')';
		
	}
	
	DB::query('INSERT INTO pre_forum_post (pid, position) VALUES ' . implode(',', $sql) . ' ON DUPLICATE KEY UPDATE position = VALUES(position)');

}*/
/*$query = DB::query('SELECT mid, btlefct FROM pkm_movedata');
$mids = [162, 283, 49, 82, 69, 101, 148, 368, 68, 243, 117, 12, 329, 90, 32, 515];
while($info = DB::fetch($query)) {
	$info['btlefct'] = str_pad($info['btlefct'], 25, '0', STR_PAD_RIGHT);
	$info['btlefct']{20} = in_array((int) $info['mid'], $mids, TRUE) ? 1 : 0;
	DB::query('UPDATE pkm_movedata SET btlefct = \'' . $info['btlefct'] . '\' WHERE mid = ' . $info['mid']);
}*/

?>