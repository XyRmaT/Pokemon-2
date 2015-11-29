<?php

/**
 *    Place
 * 1 - 6:身上
 * 7 - 饲养院
 * 8 - PC恢复
 * 9 - 丢弃
 * 10 - 交换
 * 101~200 - 箱子
 */

switch($_GET['process']) {

	case 'pcheal':

		$return['msg'] = '';

		if(is_array($_GET['heal'])) {

			//$pmcount = DB::result_first('SELECT COUNT(*) FROM pkm_mypkm WHERE uid = ' . $_G['uid'] . ' AND place IN (1, 2, 3, 4, 5, 6)');

			foreach($_GET['heal'] as $key => $val) {

				$_GET['heal'][$key] *= 1;
				$tmp = DB::fetch_first('SELECT pid FROM pkm_mypkm WHERE pid = ' . $_GET['heal'][$key] . ' AND place IN (1, 2, 3, 4, 5, 6)');

				if($_GET['heal'][$key] <= 0 || empty($tmp)) {

					unset($_GET['heal'][$key]);

					continue;

				}

				/*if($pmcount - 1 === 0) {
				
					unset($_GET['heal'][$key]);
					
					break;
					
				}
				
				--$pmcount;*/

			}

		}

		if(is_array($_GET['take'])) {

			Kit::Library('class', ['obtain']);

			$takesql = $unhealed = [];

			foreach($_GET['take'] as $key => $val) {

				$_GET['take'][$key] *= 1;
				$pokemon = DB::fetch_first('SELECT m.nickname, m.hltime, m.level, m.hp, m.iv, m.ev, m.move, p.bs FROM pkm_mypkm m LEFT JOIN pkm_pkmdata p ON m.id = p.id WHERE m.pid = ' . $_GET['take'][$key] . ' AND m.place = 8');

				if($_GET['take'][$key] <= 0 || empty($pokemon) || empty($pokemon['hltime'])) {

					unset($_GET['take'][$key]);

					continue;

				}

				$pokemon = array_merge($pokemon, Obtain::Stat($pokemon['level'], $pokemon['bs'], $pokemon['iv'], $pokemon['ev']));

				if(max(0, $pokemon['hltime'] + ceil(($pokemon['maxhp'] - $pokemon['hp']) * 6.6) - $_SERVER['REQUEST_TIME']) / 60 > 0) {

					$unhealed[] = $pokemon['nickname'];

					unset($_GET['take'][$key]);

					continue;

				}

				$place = Obtain::DepositBox($_G['uid']);

				if($place === FALSE) {

					$return['msg'] .= '箱子满了，无法取出' . (empty($takesql) ? '' : '所有') . '精灵……' . "\n";

					break;

				}

				$move = unserialize($pokemon['move']);

				foreach($move as $keyb => $valb) {

					$move[$keyb][1] = $valb[3];

				}

				$takesql[] = '(' . $val . ', ' . $place . ', ' . $pokemon['maxhp'] . ', \'' . serialize($move) . '\')';

			}

		}

		$count = [
				count($_GET['heal']),
				count($_GET['take'])
		];

		$return['console'] = implode(',', $count);

		if(!empty($unhealed)) {

			$return['msg'] .= implode('、', $unhealed) . ((count($unhealed) > 1) ? '都' : '') . '还没恢复噢，请过段时间再来看它' . ((count($unhealed) > 1) ? '们' : '') . '吧。' . "\n";

		}/* elseif(array_sum($count) === 0) {
			
			$return['msg'] = ($count[0] === 0) ? '身上必须留一只精灵哟~' : '……';
			
			break;
			
		}*/

		if($count[0] > 0) {

			$healcount = DB::result_first('SELECT COUNT(*) FROM pkm_mypkm WHERE place = 8 AND uid = ' . $_G['uid']);

			if($healcount + $count[0] > 6) {

				$_GET['heal'] = array_slice($_GET['heal'], 0, 6 - $healcount - $count[0]);
				$return['msg'] .= '虽然感到很不好意思但我们只能为每人同时照看6只精灵……' . "\n";

				if(empty($_GET['heal']))

					break;

			}

			DB::query('UPDATE pkm_mypkm SET hltime = ' . $_SERVER['REQUEST_TIME'] . ', place = 8 WHERE pid IN (' . implode(',', $_GET['heal']) . ')');

			$return['msg'] .= '您的精灵就寄放在中心了，我们会照看好您的精灵的！' . "\n";

		}

		if($count[1] > 0) {

			DB::query('INSERT INTO pkm_mypkm (pid, place, hp, move) VALUES ' . implode(',', $takesql) . ' ON DUPLICATE KEY UPDATE place = VALUES(place), hp = VALUES(hp), move = VALUES(move), STATUS = 0');

			$return['msg'] .= '您的精灵们都恢复健康了～' . "\n";

		}

		$_GET['section'] = 'pcheal';
		include ROOT . '/source/index/pkmcenter.php';


		$return['js'] = '$(\'#pc-heal ul\').empty().append(';


		foreach($pokemon as $key => $val) {
			$return['js'] .= '\'<li class="heal ' . (($key === 0) ? 'lmg-clr' : '') . '"' . (empty($val['pid']) ? ' style="visibility: hidden;"' : '') . '>\' + 
				\'<img src="' . $val['pkmimgpath'] . '"><br>\' + ' .
					'\'' . $val['nickname'] . $val['gender'] . ' Lv.' . $val['level'] . '<br>\' + ' .
					'\'<div class="bar"><div class="hp" style="width:' . $val['hpper'] . '%"></div><div class="value">' . $val['hp'] . '/' . $val['maxhp'] . '</div></div>\' + ' .
					'\'<div class="bar"><div class="exp" style="width:' . $val['expper'] . '%"></div><div class="value">' . $val['exp'] . '/' . $val['maxexp'] . '</div></div>\' + ' .
					'\'<input type="checkbox" name="heal[]" value="' . $val['pid'] . '">\' +
				\'</li>\' + ';
		}
		if($_GET['asd'] == 1) {
			print_r('<pre>');
			print_r($pokemon);
		}
		foreach($heal as $key => $val) {
			$return['js'] .= '\'<li class="take ' . (($key === 0) ? 'lmg-clr' : '') . '"' . (empty($val['pid']) ? ' style="visibility: hidden;"' : '') . '>\' + 
				\'<img src="' . $val['pkmimgpath'] . '"><br>\' + ' .
					'\'' . $val['nickname'] . $val['gender'] . ' Lv.' . $val['level'] . '<br>\' + ' .
					'\'' . (($val['fullheal'] === TRUE) ? '已恢复' : '恢复需要' . $val['hltime'][0] . '时' . $val['hltime'][1] . '分') . '\' + ' .
					'\'<input type="checkbox" name="take[]" value="' . $val['pid'] . '">\' +
				\'</li>\' + ';
		}

		$return['js'] .= '\'\');';

		break;

	case 'boxmove':

		if(empty($_GET['l']) || !is_array($_GET['l'])) {

			$return['msg'] = '你想干什么?';

			break;

		}

		$place  = $pokemon = $curplace = $unable = $sql = [];
		$query  = DB::query('SELECT pid, place FROM pkm_mypkm WHERE uid = ' . $user['uid']);
		$boxnum = $SYS['sttbox'] + $user['boxnum'];

		for($i = 1; $i <= $boxnum; $i++) {

			$place[$i + 100] = 0;

		}

		$place[1] = 0;

		while($info = DB::fetch($query)) {

			if($info['place'] > 6 && $info['place'] < 101)

				continue;

			if($info['place'] < 7)

				$info['place'] = 1;

			$place[$info['place']]  = empty($place[$info['place']]) ? 1 : ++$place[$info['place']];
			$pokemon[]              = $info['pid'];
			$curplace[$info['pid']] = $info['place'];

		}

		foreach($_GET['l'] as $key => $val) {

			if(!in_array($key, $pokemon) || !isset($place[$val]) || $curplace[$key] == $val)

				continue;

			--$place[$curplace[$key]];
			++$place[$val];

		}

		foreach($_GET['l'] as $key => $val) {

			if($curplace[$key] == $val) {

				continue;

			} elseif($val < 7 && $place[1] > 6 || $place[$val] > $SYS['perbox']) {

				$unable[$val] = !isset($unable[$val]) ? 1 : ++$unable[$val];

				continue;

			}

			$sql[] = '(' . $key . ', ' . $val . ')';

		}

		ksort($unable);

		foreach($unable as $key => $val) {

			switch($key) {
				case 1:
					$return['msg'] .= '身上的精灵太多了，' . $val . '只精灵移动失败！<br>';
					break;
				default:
					$return['msg'] .= ($key - 100) . '号箱子的精灵太多了，' . $val . '只精灵移动失败！<br>';
					break;
			}

		}

		if(!empty($sql)) {

			DB::query('INSERT INTO pkm_mypkm (pid, place) VALUES ' . implode(',', $sql) . ' ON DUPLICATE KEY UPDATE place = VALUES(place)');

			$return['msg'] .= '移动精灵成功！';

			Kit::Library('class', ['pokemon']);

			Pokemon::RefreshPartyOrder();

		}

		if(empty($unable) && empty($sql))

			$return['msg'] = '什么都没发生……';

		break;

	case 'tradesearch':

		if(empty($_GET['cdtn-username'])) {

			$return['msg'] = '请输入用户名！';

			break;

		}

		/*
			If fetchway equals to 2 use username to search user
			otherwise use uid
		*/

		//$fetchway	= min(max(1, intval($_GET['fetchway'])), 2);
		$fetchway  = 2;
		$extracol  = '';
		$cusername = !empty($_GET['cdtn-username']) ? $_GET['cdtn-username'] : '';
		$cpokemon  = !empty($_GET['cdtn-pokemon']) ? $_GET['cdtn-pokemon'] : '';
		$userinfo  = DB::fetch_first('SELECT uid, username FROM pre_common_member WHERE ' . (($fetchway === 2) ? ('username = \'' . addslashes($cusername) . '\'') : 'uid = ' . intval($_GET['value'])));

		if(empty($userinfo)) {

			$return['msg'] = '用户不存在！';

			break;

		}

		(!empty($_GET['cdtn-pokemon'])) && $extracol .= ' AND p.name = \'' . addslashes($cpokemon) . '\'';
		/*($_GET['heal'] >= 1)							&& $extracol .= ' AND m.id = ' . intval($_GET['heal']);
		($_GET['gender'] >= 0 && $_GET['gender'] < 3)	&& $extracol .= ' AND m.gender = ' . intval($_GET['gender']);
		($_GET['shiny'] >= 0 && $_GET['shiny'] < 2)		&& $extracol .= ' AND m.shiny = ' . intval($_GET['shiny']);
		($_GET['level'] >= 1 && $_GET['level'] < 101)	&& $extracol .= ' AND m.level = ' . intval($_GET['level']);*/

		/*
			Making the multipage
		*/

		$count = DB::result_first('SELECT COUNT(*) FROM pkm_mypkm m LEFT JOIN pkm_pkmdata p ON p.id = m.id WHERE m.uid = ' . $userinfo['uid'] . ' AND (m.place IN (1, 2, 3, 4, 5, 6) OR m.place > 100)' . $extracol);
		$multi = Kit::MultiPage(10, $count, 'data-urlpart="cdtn-username=' . urlencode($_GET['cdtn-username']) . '&cdtn-pokemon=' . urlencode($_GET['cdtn-pokemon']) . '"');


		/*
			Fetch hitted pokemon
		*/

		Kit::Library('class', ['obtain']);

		$query   = DB::query('SELECT m.id, m.pid, m.nickname, m.gender, m.level, m.nature, m.imgname, p.name, p.type, p.typeb, mb.username FROM pkm_mypkm m LEFT JOIN pkm_pkmdata p ON p.id = m.id LEFT JOIN pre_common_member mb ON mb.uid = m.uid WHERE m.uid = ' . $userinfo['uid'] . ' AND (m.place IN (1, 2, 3, 4, 5, 6) OR m.place > 100)' . $extracol . ' ORDER BY m.place ASC, m.id ASC LIMIT ' . $multi['start'] . ', ' . $multi['limit']);
		$pokemon = [];

		while($info = DB::fetch($query)) {

			if($info['id'] > 0) {

				$info['type']       = Obtain::TypeName($info['type'], $info['typeb']);
				$info['pkmimgpath'] = Obtain::Sprite('pokemon', 'png', $info['imgname']);
				$info['gender']     = Obtain::GenderSign($info['gender']);
				$info['nature']     = Obtain::NatureName($info['nature']);

			} else {

				$info['pkmimgpath'] = Obtain::Sprite('egg', 'png', 0);

			}

			$pokemon[] = $info;

		}

		/*
			If the target pokemon is greater than 0
			preparing to display party pokemon list
		*/

		if(!empty($pokemon)) {

			$query = DB::query('SELECT m.id, m.nickname, m.pid, m.imgname, m.level, m.gender, p.egggrp, p.egggrpb, p.name FROM pkm_mypkm m LEFT JOIN pkm_pkmdata p ON m.id = p.id WHERE m.place IN (1, 2, 3, 4, 5, 6) AND m.uid = ' . $_G['uid'] . ' AND (m.mtplace = 600 AND m.originuid != m.uid OR m.mtplace != 600) LIMIT 6');
			$party = [];

			while($info = DB::fetch($query)) {

				$info['egggrp']     = Obtain::EggGroupName($info['egggrp'], $info['egggrpb']);
				$info['pkmimgpath'] = empty($info['id']) ? Obtain::Sprite('egg', 'png', 0) : Obtain::Sprite('pokemon', 'png', $info['imgname']);
				$info['gender']     = Obtain::GenderSign($info['gender']);

				$party[] = $info;

			}

		}

		ob_start();

		$_GET['section'] = 'trade';
		$_GET['part']    = 'search';

		include template('index/pkmcenter', 'pkm');

		$return['js'] = '$("#pc-trade #res").html(\'' . str_replace(PHP_EOL, '', ob_get_clean()) . '\');';

		break;

	case 'traderequest':

		if($user['level'] < 3) {

			$return['msg'] = '不好意思！三级以下的训练师无法发送请求！';

			break;

		}

		$opid = !empty($_GET['opid']) ? intval($_GET['opid']) : 0;
		$pid  = !empty($_GET['pid']) ? intval($_GET['pid']) : 0;

		if($opid === 0 || $pid === 0) {

			$return['msg'] = '没有选择交换的精灵或交换对象！';

			break;

		}

		$count = DB::result_first('SELECT COUNT(*) FROM pkm_mypkm WHERE (pid = ' . $pid . ' AND place IN (1, 2, 3, 4, 5, 6) AND uid = ' . $user['uid'] . ' OR pid = ' . $opid . ' AND (place IN (1, 2, 3, 4, 5, 6) OR place > 100)) AND (mtplace = 600 AND originuid != uid OR mtplace != 600)');

		if($count < 2) {

			$return['msg'] = '本方或对方精灵不得为初始精灵，并且必须在身上并且对方精灵必须在身上或者箱子内才可以发出申请！';

			break;

		}

		$oppo = DB::fetch_first('SELECT uid, originuid, mtplace, (SELECT level FROM pkm_trainerdata WHERE uid = m.uid) tlevel FROM pkm_mypkm m WHERE pid = ' . $opid);

		if($oppo['tlevel'] < 3) {

			$return['msg'] = '无法对三级以下的训练师发送请求！';

			break;

		} elseif($oppo['uid'] == $user['uid']) {

			$return['msg'] = '这不就是你自己的精灵么？';

			break;

		} elseif($oppo['mtplace'] === '600' && $oppo['originuid'] === $oppo['uid']) {

			$return['msg'] = '这是对方的初始精灵，你忍心拆散他们么？';

			break;

		}

		$query = DB::query('SELECT ouid, pid FROM pkm_mytrade WHERE uid = ' . $user['uid']);
		$i     = 1;

		while($info = DB::fetch($query)) {

			if($info['ouid'] == $oppo['uid']) {

				$return['msg'] = '最多向同一个人发出1个交换请求！';

				break 2;

			} elseif($i === 3) {

				$return['msg'] = '最多发出3个交换请求！';

				break 2;

			}

			++$i;

		}


		Kit::SendMessage('精灵交换申请', $_G['username'] . '向您提出了精灵交换请求，请到<a href="?index=pc&section=trade">PC</a>查看！', $_G['uid'], $oppo['uid']);

		DB::query('UPDATE pkm_mypkm SET place = 10 WHERE pid = ' . $pid);
		DB::query('INSERT INTO pkm_mytrade (uid, ouid, pid, opid, time) VALUES (' . $user['uid'] . ', ' . $oppo['uid'] . ', ' . $pid . ', ' . $opid . ', ' . $_SERVER['REQUEST_TIME'] . ')');

		$return['msg'] = '请求发送成功！';

		break;

	case 'tradeaccept':

		$tradeid = !empty($_GET['tradeid']) ? intval($_GET['tradeid']) : 0;

		if($tradeid === 0 || !($tradeinfo = DB::fetch_first('SELECT uid, ouid, pid, opid, time FROM pkm_mytrade WHERE tradeid = ' . $tradeid . ' AND ouid = ' . $user['uid']))) {

			$return['msg'] = '不好意思，我们没有在系统里找到这个交换请求！';

			break;

		}

		/*
			@ &$info require: evldata, crritem, level, hpns, beauty, unserialized move, gender, atk, def, pv, [name, nickname]{reversed in battle}, abi, abic, form, pid, id, 
		*/


		Kit::Library('class', ['obtain', 'pokemon']);

		$query   = DB::query('SELECT m.pid, m.place, m.crritem, m.level, m.hpns, m.beauty, m.move, m.gender, m.pv, m.iv, m.ev, m.nickname, m.abi, m.form, m.originuid, m.uid, m.id, p.evldata, p.name, p.abic, p.bs FROM pkm_mypkm m LEFT JOIN pkm_pkmdata p ON p.id = m.id WHERE m.pid = ' . $tradeinfo['opid'] . ' AND m.uid = ' . $user['uid'] . ' OR m.pid = ' . $tradeinfo['pid'] . ' AND m.uid = ' . $tradeinfo['uid']);
		$pokemon = [];

		while($info = DB::fetch($query)) {

			$info         = array_merge($info, Obtain::Stat($info['level'], $info['bs'], $info['iv'], $info['ev']));
			$info['move'] = !empty($info['move']) ? unserialize($info['move']) : [];

			$pokemon[$info['pid']] = $info;

		}


		if(empty($pokemon)) {

			$return['msg'] = '身上和箱子里都没有这只精灵！';

			break;

		}

		$reqpokemon = &$pokemon[$tradeinfo['opid']];

		if($reqpokemon['place'] < 1 || $reqpokemon['place'] > 6 && $reqpokemon['place'] < 101) {

			$return['msg'] = '被请求的精灵必须在身上或者箱子内！';

			break;

		}

		$oplace = Obtain::DepositBox($tradeinfo['uid']);

		if($oplace === FALSE) {

			$return['msg'] = '对方身上和箱子都满了！';

			break;

		}

		Pokemon::Register($pokemon[$tradeinfo['pid']]['id'], !0);
		Pokemon::Register($pokemon[$tradeinfo['opid']]['id'], !0, $tradeinfo['uid']);

		//print_r($pokemon[$tradeinfo['opid']]['id'] . '.' . $tradeinfo['uid']);exit;

		sort($pokemon);

		foreach($pokemon as $key => $val) {

			Pokemon::Evolve($pokemon[$key], ['other' => !0, 'otherobj' => $pokemon[$key ^ 1]['id'], 'uid' => $pokemon[$key ^ 1]['uid']]);

		}

		DB::query('UPDATE pkm_mypkm SET place = ' . $reqpokemon['place'] . ', uid = ' . $user['uid'] . ' WHERE pid = ' . $tradeinfo['pid']);
		DB::query('UPDATE pkm_mypkm SET place = ' . $oplace . ', uid = ' . $tradeinfo['uid'] . ' WHERE pid = ' . $reqpokemon['pid']);
		DB::query('UPDATE pkm_trainerstat SET pmtrade = pmtrade + 1 WHERE uid IN (' . $user['uid'] . ', ' . $tradeinfo['uid'] . ')');
		DB::query('DELETE FROM pkm_mytrade WHERE tradeid = ' . $tradeid);

		Kit::SendMessage('精灵交换通知', $_G['username'] . '通过了您的精灵交换请求！', $_G['uid'], $tradeinfo['uid']);

		$return['msg']     = '通过了交换请求！好好照顾它啊！';
		$return['succeed'] = !0;

		break;

	case 'tradedecline':

		$tradeid = !empty($_GET['tradeid']) ? intval($_GET['tradeid']) : 0;

		if($tradeid === 0 || !($tradeinfo = DB::fetch_first('SELECT uid, pid FROM pkm_mytrade WHERE tradeid = ' . $tradeid . ' AND ouid = ' . $user['uid']))) {

			$return['msg'] = '不好意思，我们没有在系统里找到这个交换请求！';

			break;

		}

		Kit::Library('class', ['obtain']);

		$oplace = Obtain::DepositBox($tradeinfo['uid']);

		if($oplace === FALSE) {

			$return['msg'] = '对方身上和箱子都满了！暂时无法拒绝！';

			break;

		}

		DB::query('DELETE FROM pkm_mytrade WHERE tradeid = ' . $tradeid);
		DB::query('UPDATE pkm_mypkm SET place = ' . $oplace . ' WHERE pid = ' . $tradeinfo['pid']);

		Kit::SendMessage('精灵交换通知', $_G['username'] . '拒绝了您的精灵交换请求！', $_G['uid'], $tradeinfo['uid']);

		$return['msg']     = '拒绝了交换请求！';
		$return['succeed'] = !0;

		break;

	case 'tradecancel':

		$tradeid = !empty($_GET['tradeid']) ? intval($_GET['tradeid']) : 0;

		if($tradeid === 0 || !($tradeinfo = DB::fetch_first('SELECT ouid, pid FROM pkm_mytrade WHERE tradeid = ' . $tradeid . ' AND uid = ' . $user['uid']))) {

			$return['msg'] = '不好意思，我们没有在系统里找到这个交换请求！';

			break;

		}

		Kit::Library('class', ['obtain']);

		$place = Obtain::DepositBox($user['uid']);

		if($place === FALSE) {

			$return['msg'] = '身上和箱子都满了！';

			break;

		}

		DB::query('DELETE FROM pkm_mytrade WHERE tradeid = ' . $tradeid);
		DB::query('UPDATE pkm_mypkm SET place = ' . $place . ' WHERE pid = ' . $tradeinfo['pid']);

		Kit::SendMessage('精灵交换通知', $_G['username'] . '取消了精灵交换请求！', $_G['uid'], $tradeinfo['ouid']);

		$return['msg']     = '取消了交换请求！';
		$return['succeed'] = !0;

		break;

}