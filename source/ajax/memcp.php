<?php

switch($process) {

    case 'pmabandon':

        $pkm_id = isset($_GET['pkm_id']) ? intval($_GET['pkm_id']) : 0;

        if(DB::result_first('SELECT COUNT(*) FROM pkm_mypkm WHERE uid = ' . $trainer['uid']) === '0') {
            $return['msg'] = '一只精灵都没有怎么行？';
            break;
        }

        $info = DB::fetch_first('SELECT nat_id, location, uid_initial, uid, met_location FROM pkm_mypkm WHERE pkm_id = ' . $pkm_id);

        if(!in_array($info['location'], range(1, 6))) {
            $return['msg'] = '精灵不在身上，无法丢弃！';
            break;
        } elseif($info['met_location'] === '600') {
            $return['msg'] = '最初的伙伴你怎能忍心！？';
            break;
        }

        DB::query('UPDATE pkm_mypkm SET uid = 0, location = 9 WHERE pkm_id = ' . $pkm_id);

        if($info['nat_id'] !== '0')
            $trainer['addexp'] -= ($info['uid_initial'] === $info['uid']) ? 8 : 2;
        else
            ($info['uid_initial'] === $info['uid']) || ($trainer['addexp'] -= 8);

        ob_start();

        $_GET['section'] = '';

        include ROOT . '/source/index/memcp.php';
        include template('index/memcp', 'pkm');

        $return['include'] = '$(".my-info").html(\'' . str_replace(PHP_EOL, '', ob_get_clean()) . '\');';

        break;

    case 'pmnickname':

        $return['console'] = DB::query('UPDATE pkm_mypkm SET nickname = \'' . mb_substr(urldecode($_GET['nickname']), 0, 6, 'utf-8') . '\' WHERE id != 0 AND pkm_id = ' . intval($_GET['pkm_id'])) ? 'Success.' : 'Failed.';

        ob_start();

        $_GET['section'] = '';

        include ROOT . '/source/index/memcp.php';
        include template('index/memcp', 'pkm');

        $return['include'] = '$(".my-info").html(\'' . str_replace(PHP_EOL, '', ob_get_clean()) . '\');';

        break;

    case 'pokemon-reorder':

        if(empty($_GET['orders']) || !is_array($_GET['orders'])) break;

        $i   = 1;
        $sql = '';
        foreach($_GET['orders'] as $key => &$val) {
            if(!($val = intval($val))) {
                unset($_GET['order'][$key]);
                continue;
            }
            $sql .= (!$sql ? '' : ', ') . '(' . $val . ', ' . $i . ')';
            ++$i;
        }

        if(empty($_GET['orders'])) break;

        if($sql) DB::query('INSERT INTO pkm_mypkm (pkm_id, location) VALUES ' . $sql . ' ON DUPLICATE KEY UPDATE location = VALUES(location)');

        Pokemon::RefreshPartyOrder();

        break;

    case 'give-item':

        $item_id = isset($_GET['item_id']) ? intval($_GET['item_id']) : 0;
        $pkm_id  = isset($_GET['pkm_id']) ? intval($_GET['pkm_id']) : 0;
        $pokemon = DB::fetch_first('SELECT pkm_id, nickname, item_holding FROM pkm_mypkm WHERE pkm_id = ' . $pkm_id . ' AND uid = ' . $trainer['uid']);

        if(!$pkm_id || !$pokemon) break;

        // If $item_id has a proper value, that means an item is given to the Pokemon.
        if($item_id) {
            if(!Trainer::Item('DROP', $trainer['uid'], $item_id, 1)) break;
            DB::query('UPDATE pkm_mypkm SET item_holding = ' . $item_id . ' WHERE pkm_id = ' . $pkm_id);
        }

        // If the Pokemon has a hold item, move it to the inventory no matter what.
        // If $item_id is empty, that means the operation is only returning the item but not giving.
        if($pokemon['item_holding']) {
            if(!$item_id) {
                DB::query('UPDATE pkm_mypkm SET item_holding = 0 WHERE pkm_id = ' . $pkm_id);
            }
            Trainer::Item('OBTAIN', $trainer['uid'], $pokemon['item_holding'], 1);
        }

        break;

    case 'use-item':

        $item_id = isset($_GET['item_id']) ? intval($_GET['item_id']) : 0;
        $pkm_id  = isset($_GET['pkm_id']) ? intval($_GET['pkm_id']) : 0;

        if(!$item_id || !$pkm_id) {
            $return['msg'] = Obtain::Text('no_pokemon_or_item');
            break;
        }

        $item = DB::fetch_first('SELECT mi.quantity, i.name_zh name, i.effect, i.is_usable, i.type
                                 FROM pkm_myitem mi LEFT JOIN pkm_itemdata i ON mi.item_id = i.item_id
                                 WHERE mi.quantity > 0 AND mi.item_id = ' . $item_id . ' AND mi.uid = ' . $trainer['uid']);

        if(empty($item)) {
            $return['msg'] = Obtain::Text('no_such_item');
        } elseif($item['quantity'] <= 0) {
            $return['msg'] = Obtain::Text('no_more_item');
        } elseif(!$item['is_usable']) {
            $return['msg'] = Obtain::Text('item_not_usable');
        } elseif(!$item['effect'] && $item['type'] != ITEM_TYPE_EVOSTONE) {
            $return['msg'] = Obtain::Text('item_no_effect');
        } else {
            $effect         = [];
            $return['msg']  = '';
            $item['effect'] = explode('|', preg_replace('/\s/', '', $item['effect']));

            if(is_array($item['effect'])) {
                foreach($item['effect'] as $val) {
                    $temp             = explode(':', $val);
                    $effect[$temp[0]] = $temp[1];
                }
            }

            // Didn't extract columns beauty, moves, id from the database for evolution
            // because they are useless due to no one need them to evolve by using an item
            $pokemon = DB::fetch_first('SELECT ' . Kit::FetchFields([FIELDS_POKEMON_BASIC, FIELDS_POKEMON_LEVELUP]) . '
                                        FROM pkm_mypkm m
                                        LEFT JOIN pkm_pkmdata p ON m.nat_id = p.nat_id
                                        WHERE m.pkm_id = ' . $pkm_id);

            if(empty($pokemon)) {
                $return['msg'] = Obtain::Text('no_such_pokemon');
            } elseif(!$pokemon['nat_id']) {
                $return['msg'] = Obtain::Text('use_item_on_egg');
            } elseif($pokemon['location'] > 6) {
                $return['msg'] = Obtain::Text('not_in_party', [$pokemon['nickname']]);
            } elseif($pokemon['hp'] <= 0 && (!empty($effect['hp']) || !empty($effect['status']))) {
                $return['msg'] = Obtain::Text('item_use_on_fainted', [$pokemon['nickname']]);
            } else {

                if($item['type'] == ITEM_TYPE_MEDICINE) {

                    $pokemon     = array_merge($pokemon, Obtain::Stat($pokemon['level'], $pokemon['base_stat'], $pokemon['ind_value'], $pokemon['eft_value']));
                    $succeed     = $evolved = FALSE;
                    $effectcount = 0;

                    foreach($effect as $key => $val) {
                        switch($key) {
                            case 'hp':
                                if($pokemon['hp'] == $pokemon['max_hp']) break;
                                $pokemon['hp'] += min((substr($val, -1, 1) === '%') ? floor($pokemon['max_hp'] * $val / 100) : $val, $pokemon['max_hp'] - $pokemon['hp']);
                                ++$effectcount;
                                break;
                            case 'status':
                                if(!$pokemon['status'] || $val !== 'all' && $pokemon['status'] != $val) break;
                                $pokemon['status'] = 0;
                                ++$effectcount;
                                break;
                            case 'sp':
                                if(Kit::Library('db', ['item']) !== FALSE && method_exists('ItemDb', '__' . $item_id)) {
                                    ItemDb::$pokemon = &$pokemon;
                                    call_user_func(['ItemDb', '__' . $item_id]);
                                    $return['msg'] .= ItemDb::$message;
                                }
                                break;
                            case 'lvup':
                                if($pokemon['level'] < 100) {
                                    $pokemon['exp'] = Obtain::Exp($pokemon['exp_type'], min(100, $pokemon['level'] + 1));
                                    Pokemon::Levelup($pokemon, TRUE);
                                    ++$effectcount;
                                }
                                break;
                        }
                    }

                    if($effectcount > 0) $succeed = TRUE;

                } elseif($item['type'] == ITEM_TYPE_EVOSTONE) {
                    $temp    = $pokemon['nickname'];
                    $succeed = $evolved = !!Pokemon::Evolve($pokemon, ['item_used' => $item_id]);
                }

                if($succeed) {
                    Trainer::Item('DROP', $trainer['uid'], $item_id, 1, $item['quantity']);
                    if(!$evolved) {
                        DB::query('UPDATE pkm_mypkm SET hp = ' . $pokemon['hp'] . ', STATUS = ' . $pokemon['status'] . ', exp = ' . $pokemon['exp'] . ' WHERE pkm_id = ' . $pkm_id);
                        $return['msg'] .= Obtain::Text('use_item_succeed');
                    } else {
                        $return['msg'] .= Obtain::Text('pokemon_evolved', [$pokemon['nickname']]);
                    }
                } else {
                    $return['msg'] .= Obtain::Text('nothing_happened');
                }
            }
        }

        break;
    case 'pmmove':

        $pkm_id = isset($_GET['pkm_id']) ? intval($_GET['pkm_id']) : 0;
        $mid    = isset($_GET['move_id']) ? intval($_GET['move_id']) : 0;
        $lid    = isset($_GET['lid']) ? intval($_GET['lid']) : 0;

        if(empty($pkm_id) || empty($lid)) {
            $return['msg'] = '??????';
            break;
        }

        $pokemon = DB::fetch_first('SELECT moves, new_moves FROM pkm_mypkm WHERE pkm_id = ' . $pkm_id);

        if(empty($pokemon)) {
            $return['msg'] = '这是什么精灵？';
            break;
        }

        $pokemon['moves']     = unserialize($pokemon['moves']);
        $pokemon['new_moves'] = unserialize($pokemon['new_moves']);

        $key  = Kit::ColumnSearch($pokemon['moves'], 0, $mid);
        $keyb = Kit::ColumnSearch($pokemon['new_moves'], 0, $lid);

        if($keyb === FALSE) {

            $return['msg'] = '好像还不可以学这个技能。';

            break;

        } elseif(count($pokemon['moves']) >= 4 && (empty($mid) || $key === FALSE)) {

            $return['msg'] = '技能满了，无法学习！';

            break;

        }

        if($key !== FALSE) unset($pokemon['moves'][$key]);

        unset($pokemon['new_moves'][$keyb]);

        $move = DB::fetch_first('SELECT name_zh name, pp FROM pkm_movedata WHERE move_id = ' . $lid);

        if(empty($move)) {
            $return['msg'] = '无此技能数据！';
            break;
        }

        $pokemon['moves'][] = [$lid, $move['pp'], $move['name'], $move['pp'], 0];

        sort($pokemon['moves']);
        sort($pokemon['new_moves']);

        DB::query('UPDATE pkm_mypkm SET moves = \'' . serialize($pokemon['moves']) . '\', new_moves = \'' . (empty($pokemon['new_moves']) ? '' : serialize($pokemon['new_moves'])) . '\' WHERE pkm_id = ' . $pkm_id);

        $return['msg'] = '学习' . $move['name'] . '成功！';

        if(empty($pokemon['new_moves'])) {
            $return['learnmove'] = '';
        } else {
            $return['learnmove'] = '<b>学习：</b><br>';

            foreach($pokemon['new_moves'] as $key => $val)
                $return['learnmove'] .= '<input type="radio" name="lid" value="' . $val[0] . '"' . (($key === 0) ? ' checked' : '') . '> ' . $val[1] . ((($key + 1) % 2 === 0) ? '<br>' : '') . ' ';

            if(count($pokemon['moves']) > 3) {
                $return['learnmove'] .= '<br><br><b>替换：</b><br>';
                foreach($pokemon['moves'] as $key => $val)
                    $return['learnmove'] .= '<input type="radio" name="move_id" value="' . $val[0] . '"' . (($key === 0) ? ' checked' : '') . '> ' . $val[2] . ((($key + 1) % 2 === 0) ? '<br>' : '') . ' ';
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
    case 'delete-message':

        $msg_id = DB::result_first('SELECT msg_id FROM pkm_myinbox WHERE msg_id = ' . (isset($_GET['msg_id']) ? intval($_GET['msg_id']) : 0) . ' AND uid_receiver = ' . $trainer['uid']);

        if(!$msg_id) {
            $return['msg'] = '不存在！';
            break;
        }

        DB::query('DELETE FROM pkm_myinbox WHERE msg_id = ' . $msg_id);

        $_GET['section'] = 'inbox';
        include ROOT . '/source/index/memcp.php';
        $return['data'] = ['messages' => $messages, 'unread_total' => $unread];

        break;
}