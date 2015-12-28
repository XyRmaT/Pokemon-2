<?php

switch($process) {

	case 'pmabandon':

		$pid = isset($_GET['pkm_id']) ? intval($_GET['pkm_id']) : 0;

		if(DB::result_first('SELECT COUNT(*) FROM pkm_mypkm WHERE uid = ' . $trainer['uid']) === '0') {
			$return['msg'] = '一只精灵都没有怎么行？';
			break;
		}

		$info = DB::fetch_first('SELECT nat_id, location, uid_initial, uid, met_location FROM pkm_mypkm WHERE pkm_id = ' . $pid);

		if(!in_array($info['location'], range(1, 6))) {
			$return['msg'] = '精灵不在身上，无法丢弃！';
			break;
		} elseif($info['met_location'] === '600') {
			$return['msg'] = '最初的伙伴你怎能忍心！？';
			break;
		}

		DB::query('UPDATE pkm_mypkm SET uid = 0, location = 9 WHERE pkm_id = ' . $pid);

		if($info['nat_id'] !== '0')
			$trainer['addexp'] -= ($info['uid_initial'] === $info['uid']) ? 8 : 2;
		else
			($info['uid_initial'] === $info['uid']) || ($trainer['addexp'] -= 8);

		ob_start();

		$_GET['section'] = '';

		include ROOT . '/source/index/memcp.php';
		include template('index/memcp', 'pkm');

		$return['js'] = '$(".my-info").html(\'' . str_replace(PHP_EOL, '', ob_get_clean()) . '\');';

		break;

	case 'pmnickname':

		$return['console'] = DB::query('UPDATE pkm_mypkm SET nickname = \'' . mb_substr(urldecode($_GET['nickname']), 0, 6, 'utf-8') . '\' WHERE id != 0 AND pkm_id = ' . intval($_GET['pkm_id'])) ? 'Success.' : 'Failed.';

		ob_start();

		$_GET['section'] = '';

		include ROOT . '/source/index/memcp.php';
		include template('index/memcp', 'pkm');

		$return['js'] = '$(".my-info").html(\'' . str_replace(PHP_EOL, '', ob_get_clean()) . '\');';

		break;

	case 'pmreorder':

		if(!is_array($_GET['order'])) break;

		$_GET['order'] = array_values($_GET['order']);

		foreach($_GET['order'] as $key => $val) {
			$_GET['order'][$key] = $pid = intval($val);
			if($pid === 0) {
				unset($_GET['order'][$key]);
				continue;
			}
			$arr[$pid] = $key;
		}

		$dosql = [];
		$query = DB::query('SELECT pkm_id FROM pkm_mypkm WHERE pkm_id IN (' . implode(',', $_GET['order']) . ') AND location IN (1, 2, 3, 4, 5, 6)');

		while($info = DB::fetch($query))
			$dosql[] = '(' . $info['pkm_id'] . ', ' . ($arr[$info['pkm_id']] + 1) . ')';

		if(!empty($dosql)) {
			DB::query('INSERT INTO pkm_mypkm (pkm_id, location) VALUES ' . implode(',', $dosql) . ' ON DUPLICATE KEY UPDATE location = VALUES(location)');
			$return['console'] = '变更队伍顺序成功。';

		}

		break;

	case 'pmitem':

		$iid  = isset($_GET['item_id']) ? intval($_GET['item_id']) : 0;
		$pid  = isset($_GET['pkm_id']) ? intval($_GET['pkm_id']) : 0;
		$fpid = isset($_GET['fpid']) ? intval($_GET['fpid']) : 0;

		if($pid === 0) break;

		$pkm = DB::fetch_first('SELECT pkm_id, nickname, item_carrying FROM pkm_mypkm WHERE pkm_id = ' . $pid . ' AND uid = ' . $trainer['uid']);


		if(!empty($fpid)) {

			$fpokemon = DB::result_first('SELECT item_carrying FROM pkm_mypkm WHERE pkm_id = ' . $fpid . ' AND uid = ' . $trainer['uid']);

			if(empty($fpokemon)) {
				$return['console'] = '未拥有来源精灵或未携带道具。';
				break;
			}

			if(empty($pkm)) {
				$return['console'] = '未拥有目标精灵。';
				break;
			}

			if(!empty($pkm['item_carrying'])) {
				$num = DB::result_first('SELECT quantity FROM pkm_myitem WHERE item_id = ' . $pkm['item_carrying'] . ' AND uid = ' . $trainer['uid']);
				if(empty($num))
					DB::query('INSERT INTO pkm_myitem (item_id, quantity, uid) VALUES (' . $pkm['item_carrying'] . ', 1, ' . $trainer['uid'] . ')');
				else
					DB::query('UPDATE pkm_myitem SET quantity = ' . ($num + 1) . ' WHERE item_id = ' . $pkm['item_carrying'] . ' AND uid = ' . $trainer['uid']);
			}

			DB::query('UPDATE pkm_mypkm SET item_carrying = 0 WHERE pkm_id = ' . $fpid);
			DB::query('UPDATE pkm_mypkm SET item_carrying = ' . $iid . ' WHERE pkm_id = ' . $pid);

			$return['console'] = '成功！';

		} elseif($iid > 0) {

			$titem = DB::fetch_first('SELECT mi.quantity, i.name FROM pkm_myitem mi LEFT JOIN pkm_itemdata i ON mi.item_id = i.item_id WHERE mi.uid = ' . $trainer['uid'] . ' AND mi.item_id = ' . $iid);

			if(empty($titem['quantity']) || $titem['quantity'] <= 0 || empty($pkm)) break;
			elseif($titem['quantity'] - 1 <= 0)
				DB::query('DELETE FROM pkm_myitem WHERE uid = ' . $trainer['uid'] . ' AND item_id = ' . $iid);
			else
				DB::query('UPDATE pkm_myitem SET quantity = quantity - 1 WHERE uid = ' . $trainer['uid'] . ' AND item_id = ' . $iid);

			if(!empty($pkm['item_carrying'])) {
				$fitem = DB::fetch_first('SELECT quantity FROM pkm_myitem WHERE item_id = ' . $pkm['item_carrying'] . ' AND uid = ' . $trainer['uid']);
				if(empty($fitem['quantity']))
					DB::query('INSERT INTO pkm_myitem (item_id, quantity, uid) VALUES (' . $pkm['item_carrying'] . ', 1, ' . $trainer['uid'] . ')');
				else
					DB::query('UPDATE pkm_myitem SET quantity = quantity + 1 WHERE uid = ' . $trainer['uid'] . ' AND item_id = ' . $pkm['item_carrying']);
			}

			DB::query('UPDATE pkm_mypkm SET item_carrying = ' . $iid . ' WHERE pkm_id = ' . $pid);

			$return['console'] = '给' . $pkm['nickname'] . '带上了' . $titem['name'] . '。';

		} elseif(!empty($pkm['item_carrying'])) {

			DB::query('UPDATE pkm_mypkm SET item_carrying = 0 WHERE pkm_id = ' . $pid);

			$return['console'] = '把' . $pkm['nickname'] . '携带的道具卸下了。';

			$crritemnum = DB::result_first('SELECT quantity FROM pkm_myitem WHERE uid = ' . $trainer['uid'] . ' AND item_id = ' . $pkm['item_carrying']);

			if(empty($crritemnum))
				DB::query('INSERT INTO pkm_myitem (item_id, quantity, uid) VALUES (' . $pkm['item_carrying'] . ', 1, ' . $trainer['uid'] . ')');
			else
				DB::query('UPDATE pkm_myitem SET quantity = quantity + 1 WHERE item_id = ' . $pkm['item_carrying'] . ' AND uid = ' . $trainer['uid']);

		} else {
			$return['error'] = 'my_1';
			break;
		}

		break;

	case 'itemuse':

		$iid = isset($_GET['item_id']) ? intval($_GET['item_id']) : 0;
		$pid = isset($_GET['pkm_id']) ? intval($_GET['pkm_id']) : 0;

		if($iid === 0 || $pid === 0) {
			$return['error'] = 'my_2';
			break;
		}

		$item = DB::fetch_first('SELECT mi.quantity, i.name, i.effect, i.is_usable, i.type FROM pkm_myitem mi LEFT JOIN pkm_itemdata i ON mi.item_id = i.item_id WHERE mi.item_id = ' . $iid . ' AND mi.uid = ' . $trainer['uid']);

		if(empty($item))
			$return['msg'] = '咦，背包里哪来的这个？交给警察叔叔吧！';
		elseif($item['quantity'] <= 0)
			$return['msg'] = '瞧我这记性！全部都用掉了……';
		elseif($item['is_usable'] === '0')
			$return['msg'] = '我去这道具居然用不了！';
		elseif($item['effect'] === '' && $item['type'] !== '2')
			$return['msg'] = '这个道具毫无用处……';

		else {

			$effect        = [];
			$return['msg'] = '';

			if(!empty($item['effect']))

				foreach(explode('|', $item['effect']) as $val) {

					$tmp             = explode(':', $val);
					$effect[$tmp[0]] = $tmp[1];

				}

			/*
				Didn't extract columns beauty, moves, id from the database for evolution
				because they are useless due to no one need them to evolve by using an item
			*/

			$pokemon = DB::fetch_first('SELECT m.pkm_id, m.nat_id, m.nickname, m.happiness, m.level, m.hp, m.location, m.ind_value, m.eft_value, m.psn_value, m.ability, m.item_carrying, m.gender, m.form, m.status, m.exp, p.base_stat, p.ability_hidden, p.evolution_data, p.name, p.exp_type FROM pkm_mypkm m LEFT JOIN pkm_pkmdata p ON m.nat_id = p.nat_id WHERE m.pkm_id = ' . $pid);

			if(empty($pokemon))

				$return['msg'] = '这是哪位？我有这只精灵么？';

			elseif(empty($pokemon['nat_id']))

				$return['msg'] = '对一个精灵蛋使用道具……？';

			elseif($pokemon['location'] > 6)

				$return['msg'] = $pokemon['nickname'] . '没带在身上……';

			elseif($pokemon['hp'] <= 0 && (!empty($effect['hp']) || !empty($effect['status'])))

				$return['msg'] = $pokemon['nickname'] . '伤得太重了，这个道具没效果！';

			else {

				if($item['type'] === '4') {

					Kit::Library('class', ['obtain']);

					$pokemon     = array_merge($pokemon, Obtain::Stat($pokemon['level'], $pokemon['base_stat'], $pokemon['ind_value'], $pokemon['eft_value']));
					$success     = $evolve = FALSE;
					$effectcount = 0;

					foreach($effect as $key => $val) {
						switch($key) {
							default:
                                continue;
								break;
							case 'hp':
								if($pokemon['hp'] == $pokemon['maxhp']) break;
								$pokemon['hp'] += min((substr($val, -1, 1) === '%') ? floor($pokemon['maxhp'] * $val / 100) : $val, $pokemon['maxhp'] - $pokemon['hp']);
								$success = TRUE;
								++$effectcount;
								break;
							case 'status':
								if($pokemon['status'] === '0' || $val !== 'all' && $pokemon['status'] == $val) break;
								$pokemon['status'] = 0;
								++$effectcount;
								break;
							case 'sp':
								if(Kit::Library('db', ['item']) !== FALSE && ($tmp = new ItemDb()) && method_exists($tmp, '__' . $iid)) {
									ItemDb::$pokemon = &$pokemon;
									call_user_func(['ItemDb', '__' . $iid]);
									if(!empty(ItemDb::$message))
										$return['msg'] .= ItemDb::$message . "\n";
								}
								break;
							case 'lvup':
								if($pokemon['level'] < 100) {
									$pokemon['exp'] = Obtain::Exp($pokemon['exp_type'], min(100, $pokemon['level'] + 1));
									++$effectcount;
								}
								break;
						}
					}

					if($effectcount > 0) $success = TRUE;

				} elseif($item['type'] === '2') {
					Kit::Library('class', ['pokemon', 'obtain']);
					$tmp = $pokemon['nickname'];
					if(Pokemon::Evolve($pokemon, ['useitem' => $iid]))
						$success = $evolve = TRUE;
				}

				if($success === TRUE) {

					if($item['quantity'] - 1 <= 0)
						DB::query('DELETE FROM pkm_myitem WHERE item_id = ' . $iid . ' AND uid = ' . $trainer['uid']);
					else
						DB::query('UPDATE pkm_myitem SET quantity = quantity - 1 WHERE item_id = ' . $iid . ' AND uid = ' . $trainer['uid']);

					if($evolve === FALSE) {
						DB::query('UPDATE pkm_mypkm SET hp = ' . $pokemon['hp'] . ', status = ' . $pokemon['status'] . ', exp = ' . $pokemon['exp'] . ' WHERE pkm_id = ' . $pid);
						$return['msg'] .= '道具使用成功！' . "\n";
					} else {
						$return['msg'] .= $tmp . '进化了！' . "\n";
					}

					ob_start();

					$_GET['section'] = 'inventory';

					include ROOT . '/source/index/memcp.php';
					include template('index/memcp', 'pkm');

					$return['js'] = 'item = ' . $item . ';$(".my-info").html(\'' . str_replace(PHP_EOL, '', ob_get_clean()) . '\');';

				} else
					$return['msg'] .= '什么都没有发生……';
			}

		}

		break;
	case 'pmmove':

		$pid = isset($_GET['pkm_id']) ? intval($_GET['pkm_id']) : 0;
		$mid = isset($_GET['move_id']) ? intval($_GET['move_id']) : 0;
		$lid = isset($_GET['lid']) ? intval($_GET['lid']) : 0;

		if(empty($pid) || empty($lid)) {

			$return['msg'] = '??????';

			break;

		}

		$pokemon = DB::fetch_first('SELECT moves, moves_new FROM pkm_mypkm WHERE pkm_id = ' . $pid);

		if(empty($pokemon)) {

			$return['msg'] = '这是什么精灵？';

			break;

		}

		$pokemon['moves']    = unserialize($pokemon['moves']);
		$pokemon['moves_new'] = unserialize($pokemon['moves_new']);

		$key  = Kit::ColumnSearch($pokemon['moves'], 0, $mid);
		$keyb = Kit::ColumnSearch($pokemon['moves_new'], 0, $lid);

		if($keyb === FALSE) {

			$return['msg'] = '好像还不可以学这个技能。';

			break;

		} elseif(count($pokemon['moves']) >= 4 && (empty($mid) || $key === FALSE)) {

			$return['msg'] = '技能满了，无法学习！';

			break;

		}

		if($key !== FALSE) unset($pokemon['moves'][$key]);

		unset($pokemon['moves_new'][$keyb]);

		$move = DB::fetch_first('SELECT name_zh name, pp FROM pkm_movedata WHERE move_id = ' . $lid);

		if(empty($move)) {

			$return['msg'] = '无此技能数据！';

			break;

		}

		$pokemon['moves'][] = [$lid, $move['pp'], $move['name'], $move['pp'], 0];

		sort($pokemon['moves']);
		sort($pokemon['moves_new']);

		DB::query('UPDATE pkm_mypkm SET moves = \'' . serialize($pokemon['moves']) . '\', moves_new = \'' . (empty($pokemon['moves_new']) ? '' : serialize($pokemon['moves_new'])) . '\' WHERE pkm_id = ' . $pid);

		$return['msg'] = '学习' . $move['name'] . '成功！';

		if(empty($pokemon['moves_new'])) {

			$return['learnmove'] = '';

		} else {

			$return['learnmove'] = '<b>学习：</b><br>';

			foreach($pokemon['moves_new'] as $key => $val) {

				$return['learnmove'] .= '<input type="radio" name="lid" value="' . $val[0] . '"' . (($key === 0) ? ' checked' : '') . '> ' . $val[1] . ((($key + 1) % 2 === 0) ? '<br>' : '') . ' ';

			}

			if(count($pokemon['moves']) > 3) {

				$return['learnmove'] .= '<br><br><b>替换：</b><br>';

				foreach($pokemon['moves'] as $key => $val) {

					$return['learnmove'] .= '<input type="radio" name="move_id" value="' . $val[0] . '"' . (($key === 0) ? ' checked' : '') . '> ' . $val[2] . ((($key + 1) % 2 === 0) ? '<br>' : '') . ' ';

				}

			}

		}

		break;

	case 'achvcheck':

		$achvid      = !empty($_GET['achv_id']) ? intval($_GET['achv_id']) : 0;
		$achievement = DB::fetch_first('SELECT ac.name, mac.dateline FROM pkm_achievementdata ac LEFT JOIN pkm_myachievement mac ON mac.achv_id = ac.achv_id AND mac.uid = ' . $trainer['uid'] . ' WHERE ac.achv_id = ' . $achvid);

		if($achvid === 0 || $achievement === FALSE) {
			$return['msg'] = '这是个什么成就？';
			break;
		} elseif(!empty($achievement['dateline'])) {
			$return['msg'] = '这个成就你已经完成了！';
			break;
		}

		Kit::Library('db', ['achievement']);

		$achvfunc = '__' . $achvid;
		$result   = method_exists('AchievementDb', $achvfunc) ? AchievementDb::$achvfunc() : !1;

		if(!$result) {

			$return['msg'] = '未满足条件啊！';

			break;

		}

		DB::query('INSERT INTO pkm_myachievement (achv_id, uid, dateline) VALUES (' . $achvid . ', ' . $trainer['uid'] . ', ' . $_SERVER['REQUEST_TIME'] . ')');

		$return['msg']     = '恭喜你完成了成就【' . $achievement['name'] . '】！';
		$return['succeed'] = !0;

		break;
	case 'inboxdel':

		$msgid = DB::result_first('SELECT msg_id FROM pkm_myinbox WHERE msg_id = ' . (isset($_GET['msg_id']) ? intval($_GET['msg_id']) : 0) . ' AND uid_receiver = ' . $trainer['uid']);

		if(!$msgid) {

			$return['msg'] = '不存在！';

			break;

		}

		DB::query('DELETE FROM pkm_myinbox WHERE msg_id = ' . $msgid);

		$return['js'] = '$(\'.del[data-msg_id=' . $msgid . ']\').eq(0).parent().parent().fadeOut(500, function() { $(this).remove(); });';
		break;
	case 'avatar-update':


		break;
    case 'login':
            App::Login(8, 'wodaxiayiado');
        break;
	default:

		ob_start();

		include ROOT . '/source/index/memcp.php';
		include template('index/memcp', 'pkm');

		$return['js'] = 'history.pushState(null, null, \'?index=my&section=' . $_GET['section'] . '\');$(\'.my-info\').html(\'' . str_replace(PHP_EOL, '', ob_get_clean()) . '\');';

		break;
}