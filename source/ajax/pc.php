<?php

switch($process) {
    case 'heal-pokemon':

        $action = !empty($_GET['action']) && $_GET['action'] === 'take' ? 'take' : '';

        $pkm_id = isset($_GET['pkm_id']) ? intval($_GET['pkm_id']) : 0;
        if(!$pkm_id) {
            $return['msg'] = Obtain::Text('illegal_pokemon');
            break;
        }

        $pokemon = DB::fetch_first('SELECT m.pkm_id, m.level, m.hp, m.eft_value, m.ind_value, m.time_pc_sent, p.base_stat
                                     FROM pkm_mypkm m
                                     LEFT JOIN pkm_pkmdata p ON p.nat_id = m.nat_id
                                     WHERE pkm_id = ' . $pkm_id . ' AND uid = ' . $trainer['uid'] . ' AND location IN (' . (!$action ? LOCATION_PARTY : LOCATION_PCHEAL) . ')');
        if(!$pokemon) {
            $return['msg'] = Obtain::Text('illegal_pokemon');
            break;
        }

        if(!$action) {
            $heal_count = DB::result_first('SELECT COUNT(*) FROM pkm_mypkm WHERE uid = ' . $trainer['uid'] . ' AND location = ' . LOCATION_PCHEAL);
            if($heal_count >= $system['pkm_limits']['pc_heal']) {
                $return['msg'] = Obtain::Text('pc_heal_full', [$system['pkm_limits']['pc_heal']]);
                break;
            }
            Pokemon::MoveLocation($pkm_id, LOCATION_PCHEAL, ['time_pc_sent' => $_SERVER['REQUEST_TIME']]);
        } else {
            $moved_to = Obtain::DepositBox($trainer['uid']);
            if($moved_to === FALSE) {
                $return['msg'] = Obtain::Text('locations_full');
                break;
            }
            $pokemon = array_merge($pokemon, Obtain::Stat($pokemon['level'], $pokemon['base_stat'], $pokemon['ind_value'], $pokemon['eft_value']));
            if(Obtain::HealRemainTime($pokemon['time_pc_sent'], $pokemon['max_hp'], $pokemon['hp']) > 0) {
                $return['msg'] = Obtain::Text('pokemon_not_recovered');
                break;
            }
            Pokemon::MoveLocation($pkm_id, $moved_to, ['time_pc_sent' => 0, 'hp' => $pokemon['max_hp']]);
        }

        $_GET['section'] = 'heal';
        include ROOT . '/source/index/pc.php';
        $return['data'] = ['heal' => $heal, 'party' => $party];

        break;
    case 'boxmove':

        if(empty($_GET['l']) || !is_array($_GET['l'])) {
            $return['msg'] = '你想干什么?';
            break;
        }

        $location = $pokemon = $curplace = $unable = $sql = [];
        $query    = DB::query('SELECT pkm_id, location FROM pkm_mypkm WHERE uid = ' . $trainer['uid']);
        $boxnum   = $system['initial_box'] + $trainer['box_quantity'];

        for($i = 1; $i <= $boxnum; $i++)
            $location[$i + 100] = 0;

        $location[1] = 0;

        while($info = DB::fetch($query)) {

            if($info['location'] > 6 && $info['location'] < 101) continue;
            if($info['location'] < 7) $info['location'] = 1;

            $location[$info['location']] = empty($location[$info['location']]) ? 1 : ++$location[$info['location']];
            $pokemon[]                   = $info['pkm_id'];
            $curplace[$info['pkm_id']]   = $info['location'];

        }

        foreach($_GET['l'] as $key => $val) {
            if(!in_array($key, $pokemon) || !isset($location[$val]) || $curplace[$key] == $val) continue;
            --$location[$curplace[$key]];
            ++$location[$val];
        }

        foreach($_GET['l'] as $key => $val) {
            if($curplace[$key] == $val) {
                continue;
            } elseif($val < 7 && $location[1] > 6 || $location[$val] > $system['pkm_per_box']) {
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
            DB::query('INSERT INTO pkm_mypkm (pkm_id, location) VALUES ' . implode(',', $sql) . ' ON DUPLICATE KEY UPDATE location = VALUES(location)');
            $return['msg'] .= '移动精灵成功！';
            Kit::Library('class', ['pokemon']);
            Pokemon::RefreshPartyOrder();
        }

        if(empty($unable) && empty($sql)) $return['msg'] = '什么都没发生……';

        break;

    case 'tradesearch':

        if(empty($_GET['cdtn-username'])) {
            $return['msg'] = '请输入用户名！';
            break;
        }

        // If fetchway equals to 2 use username to search user otherwise use uid

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
        /*($_GET['heal'] >= 1)							&& $extracol .= ' AND m.nat_id = ' . intval($_GET['heal']);
        ($_GET['gender'] >= 0 && $_GET['gender'] < 3)	&& $extracol .= ' AND m.gender = ' . intval($_GET['gender']);
        ($_GET['is_shiny'] >= 0 && $_GET['is_shiny'] < 2)		&& $extracol .= ' AND m.is_shiny = ' . intval($_GET['is_shiny']);
        ($_GET['level'] >= 1 && $_GET['level'] < 101)	&& $extracol .= ' AND m.level = ' . intval($_GET['level']);*/

        /*
            Making the multipage
        */

        $count = DB::result_first('SELECT COUNT(*) FROM pkm_mypkm m LEFT JOIN pkm_pkmdata p ON p.nat_id = m.nat_id WHERE m.uid = ' . $userinfo['uid'] . ' AND (m.location IN (1, 2, 3, 4, 5, 6) OR m.location > 100)' . $extracol);
        $multi = Kit::MultiPage(10, $count, 'data-urlpart="cdtn-username=' . urlencode($_GET['cdtn-username']) . '&cdtn-pokemon=' . urlencode($_GET['cdtn-pokemon']) . '"');


        /*
            Fetch hitted pokemon
        */

        Kit::Library('class', ['obtain']);

        $query   = DB::query('SELECT m.nat_id, m.pkm_id, m.nickname, m.gender, m.level, m.nature, m.sprite_name, p.name_zh name, p.type, p.type_b, mb.username
                              FROM pkm_mypkm m
                              LEFT JOIN pkm_pkmdata p ON p.nat_id = m.nat_id
                              LEFT JOIN pre_common_member mb ON mb.uid = m.uid
                              WHERE m.uid = ' . $userinfo['uid'] . ' AND (m.location IN (1, 2, 3, 4, 5, 6) OR m.location > 100) ' . ($extracol .= ' ') .
            'ORDER BY m.location ASC, m.nat_id ASC LIMIT ' . $multi['start'] . ', ' . $multi['limit']);
        $pokemon = [];

        while($info = DB::fetch($query)) {
            if($info['nat_id'] > 0) {
                $info['type']       = Obtain::TypeName($info['type'], $info['type_b']);
                $info['pkm_sprite'] = Obtain::Sprite('pokemon', 'gif', $info['sprite_name']);
                $info['gender']     = Obtain::GenderSign($info['gender']);
                $info['nature']     = Obtain::NatureName($info['nature']);
            } else {
                $info['pkm_sprite'] = Obtain::Sprite('egg', 'png', 0);
            }
            $pokemon[] = $info;
        }

        /*
            If the target pokemon is greater than 0
            preparing to display party pokemon list
        */

        if(!empty($pokemon)) {

            $query = DB::query('SELECT m.nat_id, m.nickname, m.pkm_id, m.sprite_name, m.level, m.gender, p.egg_group, p.egg_group_b, p.name_zh name FROM pkm_mypkm m LEFT JOIN pkm_pkmdata p ON m.nat_id = p.nat_id WHERE m.location IN (1, 2, 3, 4, 5, 6) AND m.uid = ' . $trainer['uid'] . ' AND (m.met_location = 600 AND m.uid_initial != m.uid OR m.met_location != 600) LIMIT 6');
            $party = [];

            while($info = DB::fetch($query)) {
                $info['egg_group']  = Obtain::EggGroupName($info['egg_group'], $info['egg_group_b']);
                $info['pkm_sprite'] = empty($info['nat_id']) ? Obtain::Sprite('egg', 'png', 0) : Obtain::Sprite('pokemon', 'gif', $info['sprite_name']);
                $info['gender']     = Obtain::GenderSign($info['gender']);
                $party[]            = $info;
            }
        }

        ob_start();

        $_GET['section'] = 'trade';
        $_GET['part']    = 'search';

        include template('index/pc', 'pkm');

        $return['js'] = '$("#pc-trade #res").html(\'' . str_replace(PHP_EOL, '', ob_get_clean()) . '\');';

        break;

    case 'traderequest':

        if($trainer['level'] < 3) {
            $return['msg'] = '不好意思！三级以下的训练师无法发送请求！';
            break;
        }

        $opid = !empty($_GET['pkm_id_target']) ? intval($_GET['pkm_id_target']) : 0;
        $pid  = !empty($_GET['pkm_id']) ? intval($_GET['pkm_id']) : 0;

        if($opid === 0 || $pid === 0) {
            $return['msg'] = '没有选择交换的精灵或交换对象！';
            break;
        }

        $count = DB::result_first('SELECT COUNT(*) FROM pkm_mypkm WHERE (pkm_id = ' . $pid . ' AND location IN (1, 2, 3, 4, 5, 6) AND uid = ' . $trainer['uid'] . ' OR pkm_id = ' . $opid . ' AND (location IN (1, 2, 3, 4, 5, 6) OR location > 100)) AND (met_location = 600 AND uid_initial != uid OR met_location != 600)');

        if($count < 2) {
            $return['msg'] = '本方或对方精灵不得为初始精灵，并且必须在身上并且对方精灵必须在身上或者箱子内才可以发出申请！';
            break;
        }

        $oppo = DB::fetch_first('SELECT uid, uid_initial, met_location, (SELECT level FROM pkm_trainerdata WHERE uid = m.uid) trainer_level FROM pkm_mypkm m WHERE pkm_id = ' . $opid);

        if($oppo['trainer_level'] < 3) {
            $return['msg'] = '无法对三级以下的训练师发送请求！';
            break;
        } elseif($oppo['uid'] == $trainer['uid']) {
            $return['msg'] = '这不就是你自己的精灵么？';
            break;
        } elseif($oppo['met_location'] === '600' && $oppo['uid_initial'] === $oppo['uid']) {
            $return['msg'] = '这是对方的初始精灵，你忍心拆散他们么？';
            break;
        }

        $query = DB::query('SELECT uid_target, pkm_id FROM pkm_mytrade WHERE uid = ' . $trainer['uid']);
        $i     = 1;

        while($info = DB::fetch($query)) {
            if($info['uid_target'] == $oppo['uid']) {
                $return['msg'] = '最多向同一个人发出1个交换请求！';
                break 2;
            } elseif($i === 3) {
                $return['msg'] = '最多发出3个交换请求！';
                break 2;
            }
            ++$i;
        }


        Kit::SendMessage('精灵交换申请', $trainer['username'] . '向您提出了精灵交换请求，请到<a href="?index=pc&section=trade">PC</a>查看！', $trainer['uid'], $oppo['uid']);

        DB::query('UPDATE pkm_mypkm SET location = 10 WHERE pkm_id = ' . $pid);
        DB::query('INSERT INTO pkm_mytrade (uid, uid_target, pkm_id, pkm_id_target, time) VALUES (' . $trainer['uid'] . ', ' . $oppo['uid'] . ', ' . $pid . ', ' . $opid . ', ' . $_SERVER['REQUEST_TIME'] . ')');

        $return['msg'] = '请求发送成功！';

        break;

    case 'tradeaccept':

        $tradeid = !empty($_GET['pkm_id']) ? intval($_GET['pkm_id']) : 0;

        if($tradeid === 0 || !($tradeinfo = DB::fetch_first('SELECT uid, uid_target, pkm_id, pkm_id_target, time FROM pkm_mytrade WHERE pkm_id = ' . $tradeid . ' AND uid_target = ' . $trainer['uid']))) {
            $return['msg'] = '不好意思，我们没有在系统里找到这个交换请求！';
            break;
        }

        /*
            @ &$info require: evolution_data, item_holding, level, happiness, beauty, unserialized moves, gender, atk, def, psn_value, [name, nickname]{reversed in battle}, ability, ability_hidden, form, pkm_id, id,
        */


        Kit::Library('class', ['obtain', 'pokemon']);

        $query   = DB::query('SELECT m.pkm_id, m.location, m.item_holding, m.level, m.happiness, m.beauty, m.moves, m.gender, m.psn_value, m.ind_value, m.eft_value, m.nickname, m.ability, m.form, m.uid_initial, m.uid, m.nat_id, p.evolution_data, p.name, p.ability_hidden, p.base_stat FROM pkm_mypkm m LEFT JOIN pkm_pkmdata p ON p.nat_id = m.nat_id WHERE m.pkm_id = ' . $tradeinfo['pkm_id_target'] . ' AND m.uid = ' . $trainer['uid'] . ' OR m.pkm_id = ' . $tradeinfo['pkm_id'] . ' AND m.uid = ' . $tradeinfo['uid']);
        $pokemon = [];

        while($info = DB::fetch($query)) {
            $info                     = array_merge($info, Obtain::Stat($info['level'], $info['base_stat'], $info['ind_value'], $info['eft_value']));
            $info['moves']            = !empty($info['moves']) ? unserialize($info['moves']) : [];
            $pokemon[$info['pkm_id']] = $info;
        }


        if(empty($pokemon)) {
            $return['msg'] = '身上和箱子里都没有这只精灵！';
            break;
        }

        $reqpokemon = &$pokemon[$tradeinfo['pkm_id_target']];

        if($reqpokemon['location'] < 1 || $reqpokemon['location'] > 6 && $reqpokemon['location'] < 101) {
            $return['msg'] = '被请求的精灵必须在身上或者箱子内！';
            break;
        }

        $oplace = Obtain::DepositBox($tradeinfo['uid']);

        if($oplace === FALSE) {
            $return['msg'] = '对方身上和箱子都满了！';
            break;
        }

        Pokemon::DexRegister($pokemon[$tradeinfo['pkm_id']]['nat_id'], !0);
        Pokemon::DexRegister($pokemon[$tradeinfo['pkm_id_target']]['nat_id'], !0, $tradeinfo['uid']);

        sort($pokemon);

        foreach($pokemon as $key => $val)
            Pokemon::Evolve($pokemon[$key], ['other' => !0, 'otherobj' => $pokemon[$key ^ 1]['nat_id'], 'uid' => $pokemon[$key ^ 1]['uid']]);

        DB::query('UPDATE pkm_mypkm SET location = ' . $reqpokemon['location'] . ', uid = ' . $trainer['uid'] . ' WHERE pkm_id = ' . $tradeinfo['pkm_id']);
        DB::query('UPDATE pkm_mypkm SET location = ' . $oplace . ', uid = ' . $tradeinfo['uid'] . ' WHERE pkm_id = ' . $reqpokemon['pkm_id']);
        DB::query('UPDATE pkm_trainerstat SET pkm_traded = pkm_traded + 1 WHERE uid IN (' . $trainer['uid'] . ', ' . $tradeinfo['uid'] . ')');
        DB::query('DELETE FROM pkm_mytrade WHERE pkm_id = ' . $tradeid);

        Kit::SendMessage('精灵交换通知', $trainer['username'] . '通过了您的精灵交换请求！', $trainer['uid'], $tradeinfo['uid']);

        $return['msg']     = '通过了交换请求！好好照顾它啊！';
        $return['succeed'] = !0;

        break;

    case 'tradedecline':

        $tradeid = !empty($_GET['pkm_id']) ? intval($_GET['pkm_id']) : 0;

        if($tradeid === 0 || !($tradeinfo = DB::fetch_first('SELECT uid, pkm_id FROM pkm_mytrade WHERE pkm_id = ' . $tradeid . ' AND uid_target = ' . $trainer['uid']))) {
            $return['msg'] = '不好意思，我们没有在系统里找到这个交换请求！';
            break;
        }

        Kit::Library('class', ['obtain']);

        $oplace = Obtain::DepositBox($tradeinfo['uid']);

        if($oplace === FALSE) {
            $return['msg'] = '对方身上和箱子都满了！暂时无法拒绝！';
            break;
        }

        DB::query('DELETE FROM pkm_mytrade WHERE pkm_id = ' . $tradeid);
        DB::query('UPDATE pkm_mypkm SET location = ' . $oplace . ' WHERE pkm_id = ' . $tradeinfo['pkm_id']);

        Kit::SendMessage('精灵交换通知', $trainer['username'] . '拒绝了您的精灵交换请求！', $trainer['uid'], $tradeinfo['uid']);

        $return['msg']     = '拒绝了交换请求！';
        $return['succeed'] = !0;

        break;

    case 'tradecancel':

        $tradeid = !empty($_GET['pkm_id']) ? intval($_GET['pkm_id']) : 0;

        if($tradeid === 0 || !($tradeinfo = DB::fetch_first('SELECT uid_target, pkm_id FROM pkm_mytrade WHERE pkm_id = ' . $tradeid . ' AND uid = ' . $trainer['uid']))) {
            $return['msg'] = '不好意思，我们没有在系统里找到这个交换请求！';
            break;
        }

        Kit::Library('class', ['obtain']);

        $location = Obtain::DepositBox($trainer['uid']);
        if($location === FALSE) {
            $return['msg'] = '身上和箱子都满了！';
            break;
        }

        DB::query('DELETE FROM pkm_mytrade WHERE pkm_id = ' . $tradeid);
        DB::query('UPDATE pkm_mypkm SET location = ' . $location . ' WHERE pkm_id = ' . $tradeinfo['pkm_id']);

        Kit::SendMessage('精灵交换通知', $trainer['username'] . '取消了精灵交换请求！', $trainer['uid'], $tradeinfo['uid_target']);

        $return['msg']     = '取消了交换请求！';
        $return['succeed'] = !0;

        break;

}