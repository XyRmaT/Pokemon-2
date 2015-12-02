<?php

switch($_GET['process']) {

	case 'pmabandon':

		$pid = isset($_GET['pid']) ? intval($_GET['pid']) : 0;

		if(DB::result_first('SELECT COUNT(*) FROM pkm_mypkm WHERE uid = ' . $trainer['uid']) === '0') {

			$return['msg'] = '一只精灵都没有怎么行？';

			break;

		}

		$info = DB::fetch_first('SELECT id, place, originuid, uid, mtplace FROM pkm_mypkm WHERE pid = ' . $pid);

		if(!in_array($info['place'], range(1, 6))) {

			$return['msg'] = '精灵不在身上，无法丢弃！';

			break;

		} elseif($info['mtplace'] === '600') {

			$return['msg'] = '最初的伙伴你怎能忍心！？';

			break;

		}

		DB::query('UPDATE pkm_mypkm SET uid = 0, place = 9 WHERE pid = ' . $pid);

		if($info['id'] !== '0')

			$trainer['addexp'] -= ($info['originuid'] === $info['uid']) ? 8 : 2;

		else

			($info['originuid'] === $info['uid']) || ($trainer['addexp'] -= 8);

		ob_start();

		$_GET['section'] = '';

		include ROOT . '/source/index/memcp.php';
		include template('index/memcp', 'pkm');

		$return['js'] = '$(".my-info").html(\'' . str_replace(PHP_EOL, '', ob_get_clean()) . '\');';

		break;

	case 'pmnickname':

		$return['console'] = DB::query('UPDATE pkm_mypkm SET nickname = \'' . mb_substr(urldecode($_GET['nickname']), 0, 6, 'utf-8') . '\' WHERE id != 0 AND pid = ' . intval($_GET['pid'])) ? 'Success.' : 'Failed.';

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
		$query = DB::query('SELECT pid FROM pkm_mypkm WHERE pid IN (' . implode(',', $_GET['order']) . ') AND place IN (1, 2, 3, 4, 5, 6)');

		while($info = DB::fetch($query)) {

			$dosql[] = '(' . $info['pid'] . ', ' . ($arr[$info['pid']] + 1) . ')';

		}

		if(!empty($dosql)) {

			DB::query('INSERT INTO pkm_mypkm (pid, place) VALUES ' . implode(',', $dosql) . ' ON DUPLICATE KEY UPDATE place = VALUES(place)');

			$return['console'] = '变更队伍顺序成功。';

		}

		break;

	case 'pmitem':

		$iid  = isset($_GET['iid']) ? intval($_GET['iid']) : 0;
		$pid  = isset($_GET['pid']) ? intval($_GET['pid']) : 0;
		$fpid = isset($_GET['fpid']) ? intval($_GET['fpid']) : 0;

		if($pid === 0) break;

		$pkm = DB::fetch_first('SELECT pid, nickname, crritem FROM pkm_mypkm WHERE pid = ' . $pid . ' AND uid = ' . $trainer['uid']);


		if(!empty($fpid)) {

			$fpokemon = DB::result_first('SELECT crritem FROM pkm_mypkm WHERE pid = ' . $fpid . ' AND uid = ' . $trainer['uid']);

			if(empty($fpokemon)) {

				$return['console'] = '未拥有来源精灵或未携带道具。';

				break;

			}

			if(empty($pkm)) {

				$return['console'] = '未拥有目标精灵。';

				break;

			}

			if(!empty($pkm['crritem'])) {

				$num = DB::result_first('SELECT num FROM pkm_myitem WHERE iid = ' . $pkm['crritem'] . ' AND uid = ' . $trainer['uid']);

				if(empty($num))

					DB::query('INSERT INTO pkm_myitem (iid, num, uid) VALUES (' . $pkm['crritem'] . ', 1, ' . $trainer['uid'] . ')');

				else

					DB::query('UPDATE pkm_myitem SET num = ' . ($num + 1) . ' WHERE iid = ' . $pkm['crritem'] . ' AND uid = ' . $trainer['uid']);

			}

			DB::query('UPDATE pkm_mypkm SET crritem = 0 WHERE pid = ' . $fpid);
			DB::query('UPDATE pkm_mypkm SET crritem = ' . $iid . ' WHERE pid = ' . $pid);

			$return['console'] = '成功！';

		} elseif($iid > 0) {

			$titem = DB::fetch_first('SELECT mi.num, i.name FROM pkm_myitem mi LEFT JOIN pkm_itemdata i ON mi.iid = i.iid WHERE mi.uid = ' . $trainer['uid'] . ' AND mi.iid = ' . $iid);

			if(empty($titem['num']) || $titem['num'] <= 0 || empty($pkm))

				break;

			elseif($titem['num'] - 1 <= 0)

				DB::query('DELETE FROM pkm_myitem WHERE uid = ' . $trainer['uid'] . ' AND iid = ' . $iid);

			else

				DB::query('UPDATE pkm_myitem SET num = num - 1 WHERE uid = ' . $trainer['uid'] . ' AND iid = ' . $iid);

			if(!empty($pkm['crritem'])) {

				$fitem = DB::fetch_first('SELECT num FROM pkm_myitem WHERE iid = ' . $pkm['crritem'] . ' AND uid = ' . $trainer['uid']);

				if(empty($fitem['num']))

					DB::query('INSERT INTO pkm_myitem (iid, num, uid) VALUES (' . $pkm['crritem'] . ', 1, ' . $trainer['uid'] . ')');

				else

					DB::query('UPDATE pkm_myitem SET num = num + 1 WHERE uid = ' . $trainer['uid'] . ' AND iid = ' . $pkm['crritem']);

			}


			DB::query('UPDATE pkm_mypkm SET crritem = ' . $iid . ' WHERE pid = ' . $pid);

			$return['console'] = '给' . $pkm['nickname'] . '带上了' . $titem['name'] . '。';

		} elseif(!empty($pkm['crritem'])) {

			DB::query('UPDATE pkm_mypkm SET crritem = 0 WHERE pid = ' . $pid);

			$return['console'] = '把' . $pkm['nickname'] . '携带的道具卸下了。';

			$crritemnum = DB::result_first('SELECT num FROM pkm_myitem WHERE uid = ' . $trainer['uid'] . ' AND iid = ' . $pkm['crritem']);

			if(empty($crritemnum))

				DB::query('INSERT INTO pkm_myitem (iid, num, uid) VALUES (' . $pkm['crritem'] . ', 1, ' . $trainer['uid'] . ')');

			else

				DB::query('UPDATE pkm_myitem SET num = num + 1 WHERE iid = ' . $pkm['crritem'] . ' AND uid = ' . $trainer['uid']);

		} else {

			$return['error'] = 'my_1';

			break;

		}

		break;

	case 'itemuse':

		$iid = isset($_GET['iid']) ? intval($_GET['iid']) : 0;
		$pid = isset($_GET['pid']) ? intval($_GET['pid']) : 0;

		if($iid === 0 || $pid === 0) {

			$return['error'] = 'my_2';

			break;

		}

		$item = DB::fetch_first('SELECT mi.num, i.name, i.effect, i.usable, i.type FROM pkm_myitem mi LEFT JOIN pkm_itemdata i ON mi.iid = i.iid WHERE mi.iid = ' . $iid . ' AND mi.uid = ' . $trainer['uid']);

		if(empty($item))

			$return['msg'] = '咦，背包里哪来的这个？交给警察叔叔吧！';

		elseif($item['num'] <= 0)

			$return['msg'] = '瞧我这记性！全部都用掉了……';

		elseif($item['usable'] === '0')

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
				Didn't extract columns beauty, move, id from the database for evolution
				because they are useless due to no one need them to evolve by using an item
			*/

			$pokemon = DB::fetch_first('SELECT m.pid, m.id, m.nickname, m.hpns, m.level, m.hp, m.place, m.iv, m.ev, m.pv, m.abi, m.crritem, m.gender, m.form, m.status, m.exp, p.bs, p.abic, p.evldata, p.name, p.exptype FROM pkm_mypkm m LEFT JOIN pkm_pkmdata p ON m.id = p.id WHERE m.pid = ' . $pid);

			if(empty($pokemon))

				$return['msg'] = '这是哪位？我有这只精灵么？';

			elseif(empty($pokemon['id']))

				$return['msg'] = '对一个精灵蛋使用道具……？';

			elseif($pokemon['place'] > 6)

				$return['msg'] = $pokemon['nickname'] . '没带在身上……';

			elseif($pokemon['hp'] <= 0 && (!empty($effect['hp']) || !empty($effect['status'])))

				$return['msg'] = $pokemon['nickname'] . '伤得太重了，这个道具没效果！';

			else {

				if($item['type'] === '4') {

					Kit::Library('class', ['obtain']);

					$pokemon     = array_merge($pokemon, Obtain::Stat($pokemon['level'], $pokemon['bs'], $pokemon['iv'], $pokemon['ev']));
					$success     = $evolve = FALSE;
					$effectcount = 0;

					foreach($effect as $key => $val) {

						switch($key) {

							default:

								continue;

								break;

							case 'hp':

								if($pokemon['hp'] == $pokemon['maxhp'])

									break;

								$pokemon['hp'] += min((substr($val, -1, 1) === '%') ? floor($pokemon['maxhp'] * $val / 100) : $val, $pokemon['maxhp'] - $pokemon['hp']);
								$success = TRUE;

								++$effectcount;

								break;

							case 'status':

								if($pokemon['status'] === '0' || $val !== 'all' && $pokemon['status'] == $val)

									break;

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

									$pokemon['exp'] = Obtain::Exp($pokemon['exptype'], min(100, $pokemon['level'] + 1));

									++$effectcount;

								}

								break;
						}
					}

					if($effectcount > 0)

						$success = TRUE;

				} elseif($item['type'] === '2') {

					Kit::Library('class', ['pokemon', 'obtain']);

					$tmp = $pokemon['nickname'];

					if(Pokemon::Evolve($pokemon, ['useitem' => $iid]))

						$success = $evolve = TRUE;

				}

				if($success === TRUE) {

					if($item['num'] - 1 <= 0)

						DB::query('DELETE FROM pkm_myitem WHERE iid = ' . $iid . ' AND uid = ' . $trainer['uid']);

					else

						DB::query('UPDATE pkm_myitem SET num = num - 1 WHERE iid = ' . $iid . ' AND uid = ' . $trainer['uid']);

					if($evolve === FALSE) {

						DB::query('UPDATE pkm_mypkm SET hp = ' . $pokemon['hp'] . ', status = ' . $pokemon['status'] . ', exp = ' . $pokemon['exp'] . ' WHERE pid = ' . $pid);

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

		$pid = isset($_GET['pid']) ? intval($_GET['pid']) : 0;
		$mid = isset($_GET['mid']) ? intval($_GET['mid']) : 0;
		$lid = isset($_GET['lid']) ? intval($_GET['lid']) : 0;

		if(empty($pid) || empty($lid)) {

			$return['msg'] = '??????';

			break;

		}

		$pokemon = DB::fetch_first('SELECT move, newmove FROM pkm_mypkm WHERE pid = ' . $pid);

		if(empty($pokemon)) {

			$return['msg'] = '这是什么精灵？';

			break;

		}

		$pokemon['move']    = unserialize($pokemon['move']);
		$pokemon['newmove'] = unserialize($pokemon['newmove']);

		$key  = Kit::ColumnSearch($pokemon['move'], 0, $mid);
		$keyb = Kit::ColumnSearch($pokemon['newmove'], 0, $lid);

		if($keyb === FALSE) {

			$return['msg'] = '好像还不可以学这个技能。';

			break;

		} elseif(count($pokemon['move']) >= 4 && (empty($mid) || $key === FALSE)) {

			$return['msg'] = '技能满了，无法学习！';

			break;

		}

		if($key !== FALSE) unset($pokemon['move'][$key]);

		unset($pokemon['newmove'][$keyb]);

		$move = DB::fetch_first('SELECT name_zh, pp FROM pkm_movedata WHERE mid = ' . $lid);

		if(empty($move)) {

			$return['msg'] = '无此技能数据！';

			break;

		}

		$pokemon['move'][] = [$lid, $move['pp'], $move['name'], $move['pp'], 0];

		sort($pokemon['move']);
		sort($pokemon['newmove']);

		DB::query('UPDATE pkm_mypkm SET move = \'' . serialize($pokemon['move']) . '\', newmove = \'' . (empty($pokemon['newmove']) ? '' : serialize($pokemon['newmove'])) . '\' WHERE pid = ' . $pid);

		$return['msg'] = '学习' . $move['name'] . '成功！';

		if(empty($pokemon['newmove'])) {

			$return['learnmove'] = '';

		} else {

			$return['learnmove'] = '<b>学习：</b><br>';

			foreach($pokemon['newmove'] as $key => $val) {

				$return['learnmove'] .= '<input type="radio" name="lid" value="' . $val[0] . '"' . (($key === 0) ? ' checked' : '') . '> ' . $val[1] . ((($key + 1) % 2 === 0) ? '<br>' : '') . ' ';

			}

			if(count($pokemon['move']) > 3) {

				$return['learnmove'] .= '<br><br><b>替换：</b><br>';

				foreach($pokemon['move'] as $key => $val) {

					$return['learnmove'] .= '<input type="radio" name="mid" value="' . $val[0] . '"' . (($key === 0) ? ' checked' : '') . '> ' . $val[2] . ((($key + 1) % 2 === 0) ? '<br>' : '') . ' ';

				}

			}

		}

		break;

	case 'achvcheck':

		$achvid      = !empty($_GET['achvid']) ? intval($_GET['achvid']) : 0;
		$achievement = DB::fetch_first('SELECT ac.name, mac.dateline FROM pkm_achievementdata ac LEFT JOIN pkm_myachievement mac ON mac.achvid = ac.achvid AND mac.uid = ' . $trainer['uid'] . ' WHERE ac.achvid = ' . $achvid);

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

		DB::query('INSERT INTO pkm_myachievement (achvid, uid, dateline) VALUES (' . $achvid . ', ' . $trainer['uid'] . ', ' . $_SERVER['REQUEST_TIME'] . ')');

		$return['msg']     = '恭喜你完成了成就【' . $achievement['name'] . '】！';
		$return['succeed'] = !0;

		break;
	case 'inboxdel':

		$msgid = DB::result_first('SELECT msgid FROM pkm_myinbox WHERE msgid = ' . (isset($_GET['msgid']) ? intval($_GET['msgid']) : 0) . ' AND receiver = ' . $trainer['uid']);

		if(!$msgid) {

			$return['msg'] = '不存在！';

			break;

		}

		DB::query('DELETE FROM pkm_myinbox WHERE msgid = ' . $msgid);

		$return['js'] = '$(\'.del[data-msgid=' . $msgid . ']\').eq(0).parent().parent().fadeOut(500, function() { $(this).remove(); });';
		break;
	case 'avatar-update':


		break;
	default:

		ob_start();

		include ROOT . '/source/index/memcp.php';
		include template('index/memcp', 'pkm');

		$return['js'] = 'history.pushState(null, null, \'?index=my&section=' . $_GET['section'] . '\');$(\'.my-info\').html(\'' . str_replace(PHP_EOL, '', ob_get_clean()) . '\');';

		break;
}