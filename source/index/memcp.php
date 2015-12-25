<?php

Kit::Library('class', ['pokemon', 'obtain']);
//error_reporting(E_ALL);
//Pokemon::Generate(rand(1, 649), $trainer['uid'], array('is_shiny' => 1, 'met_level' => 20));
//if($trainer['gm']) Pokemon::Generate(290, 8, array('met_level' => 1, 'is_shiny' => 0));
//if($trainer['gm']) Pokemon::Generate(0, 4122, array('time_hatched' => 's:1', 'met_location' => 602));
//if($trainer['gm']) DB::query('UPDATE pkm_mypkm SET exp = exp + 166566, happiness = 255');
//if($trainer['gm']) DB::query('INSERT INTO pkm_myitem (item_id, quantity, uid) VALUES (32, 100, 8)');
//DB::query('UPDATE pre_common_member_count SET extcredits7 = 100000');

/*if($trainer['gm']) {
	$move = [[162, 6666, 'STABLE', 6666, 0], [47, 6666, 'SLEEP', 6666, 0], [28, 6666, 'USELESS', 6666, 0]];
	DB::query('UPDATE pkm_mypkm SET moves = \'' . serialize($move) . '\' WHERE pkm_id = 1');

}*/

$_GET['section'] = (!empty($_GET['section']) && in_array($_GET['section'], ['pokedex', 'achievement', 'inbox', 'setting', 'inventory'], TRUE)) ? $_GET['section'] : '';

$rank   = DB::result_first('SELECT COUNT(*) FROM pkm_trainerdata WHERE exp > ' . $trainer['exp']) + 1;
$reqexp = Obtain::TrainerRequireExp($trainer['level'] + 1);
$dexclt = DB::result_first('SELECT COUNT(*) FROM pkm_mypokedex WHERE uid = ' . $trainer['uid'] . ' AND is_owned = 1');

switch($_GET['section']) {
    case '':

        // Use for evolve: m.beauty, m.item_carrying, m.happiness, m.psn_value, m.ability, m.form
        $query   = DB::query('SELECT
			m.nat_id, m.pkm_id, m.gender, m.hp, m.exp, m.level, m.nature, m.nickname, m.form, m.eft_value, m.ind_value,
			m.moves_new, m.moves, m.sprite_name, m.item_captured, m.time_hatched, m.met_time, m.met_level, m.met_location,
			m.beauty, m.item_carrying, m.happiness, m.psn_value, m.form, m.uid_initial, m.status,
			a.name_zh ability,
			p.base_stat, p.type, p.type_b, p.exp_type, p.name_zh name, p.evolution_data, mb.username
			FROM pkm_mypkm m LEFT JOIN pkm_pkmdata p ON m.nat_id = p.nat_id AND m.nat_id != 0 LEFT JOIN pkm_abilitydata a ON m.ability = a.abi_id LEFT JOIN pre_common_member mb ON mb.uid = m.uid_initial WHERE m.location IN (1, 2, 3, 4, 5, 6) AND m.uid = ' . $trainer['uid'] . ' ORDER BY m.location ASC LIMIT 6');
        $pokemon = $movecriteria = [];

        while($info = DB::fetch($query)) {
            switch($info['nat_id']) {
                case '0':

                    /**
                     * [Abandoned method comment]
                     * Total hatch seconds for an time_hatched, 1275 was multiplied from 255 (variable times in the time_hatched cycle) and 5 (5 sec each step)
                     * and the part 1275 * (rand(0, 5) + $info['egg_cycle'] * 0.666) / 10 is to set a random rate of correcting the taken time.
                     */
                    //$info['maturity'] = round((time() - $info['met_time']) / $info['hatchTime'] * 100, 3);


                    if($info['time_hatched'] < $info['met_time']) {
                        $info['eggstatus'] = '这是一颗坏蛋...';
                        break;
                    }

                    $info['pkm_sprite']    = Obtain::Sprite('egg', 'png', '');
                    $info['maturity']      = min(floor(($_SERVER['REQUEST_TIME'] - $info['met_time']) / ($info['time_hatched'] - $info['met_time']) * 100), 90) + min(floor($info['exp'] / 100), 5) * 2;
                    $info['item_captured'] = Obtain::Sprite('item', 'png', 'item_' . $info['item_captured']);
                    $info['met_location']  = Obtain::MeetPlace($info['met_location']);

                    if($info['maturity'] >= 0 && $info['maturity'] < 27) $info['eggstatus'] = '毫无动静……';
                    elseif($info['maturity'] >= 27 && $info['maturity'] < 51) $info['eggstatus'] = '蛋轻微地摇了摇……';
                    elseif($info['maturity'] >= 51 && $info['maturity'] < 93) $info['eggstatus'] = '似乎从蛋里传来了声音……';
                    elseif($info['maturity'] >= 93 && $info['maturity'] < 100) $info['eggstatus'] = '蛋快孵化了！';
                    elseif($info['maturity'] >= 100) {

                        Pokemon::Hatch($info['pkm_id']);

                        $info['eggstatus'] = '呀！小蛋蛋要孵化了！';

                    }

                    $info['maturity'] .= '（' . $info['maturity'] . '%）<br>' . date('Y-m-d H:i:s', $info['time_hatched']);

                    break;
                default:

                    // Exp
                    $info = array_merge($info, Obtain::Stat($info['level'], $info['base_stat'], $info['ind_value'], $info['eft_value'], $info['nature'], $info['hp']));

                    list($info['maxexp'], $info['exp'], $info['rmexp'], $info['expper']) = Pokemon::Levelup($info);
                    Pokemon::$pmtmp = [];
                    unset($info['evolution_data'], $info['exp_type']);

                    $info['pkm_sprite']    = Obtain::Sprite('pokemon', 'gif', $info['sprite_name']);
                    $info['item_captured'] = Obtain::Sprite('item', 'png', 'item_' . $info['item_captured']);
                    $info['carry_item_sprite']   = ($info['item_carrying']) ? Obtain::Sprite('item', 'png', 'item_' . $info['item_carrying']) : '';
                    $info['gender']        = Obtain::GenderSign($info['gender']);
                    $info['type']          = Obtain::TypeName($info['type'], $info['type_b'], TRUE, ' blk-c');
                    $info['met_location']  = Obtain::MeetPlace($info['met_location']);
                    $info['nature']        = Obtain::NatureName($info['nature']);
                    $info['status']        = Obtain::StatusIcon($info['status']);
                    $info['met_time']      = date('Y年m月d日', $info['met_time']);

                    if($info['happiness'] < 50) $info['hpnsstatus'] = '用陌生而又警惕的眼神望着你。';
                    elseif($info['happiness'] >= 50 && $info['happiness'] < 90) $info['hpnsstatus'] = '与你的感情还算可以。';
                    elseif($info['happiness'] >= 90 && $info['happiness'] < 150) $info['hpnsstatus'] = '渐渐开始缠着你了……！';
                    elseif($info['happiness'] >= 150 && $info['happiness'] < 220) $info['hpnsstatus'] = '你们之间的羁绊越来越深了！';
                    elseif($info['happiness'] >= 220) $info['hpnsstatus'] = '没有人可以让你们分开了！';

                    $info['moves_new'] = !empty($info['moves_new']) ? explode(',', $info['moves_new']) : [];
                    $info['moves']     = !empty($info['moves']) ? unserialize($info['moves']) : [];

                    // Creating a query clause string for move data fetching preparation
                    foreach($info['moves'] as $val) $movecriteria[] = $val['move_id'];
                    $movecriteria = array_merge($movecriteria, $info['moves_new']);

                    break;
            }

            $pokemon[] = $info;
        }

        if($movecriteria) {
            $query = DB::query('SELECT move_id, name_zh name, type, power, class FROM pkm_movedata WHERE move_id IN (' . implode(',', $movecriteria) . ')');
            $moves = [];
            while($info = DB::fetch($query))
                $moves[$info['move_id']] = [
                    'name'       => $info['name'],
                    'type'       => $info['type'],
                    'power'      => $info['power'],
                    'class_name' => Obtain::MoveClassName($info['class']),
                    'type_name'  => Obtain::TypeName($info['type'])
                ];
        }

        break;

    case 'setting':
        if(!empty($trainer['style']) && $handle = opendir(ROOT_TEMPLATE)) {
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
        $count   = DB::result_first('SELECT COUNT(DISTINCT nat_id) FROM pkm_pkmdata');
        $query   = DB::query('SELECT md.nat_id, md.is_owned, p.name_zh name, p.type, p.type_b FROM pkm_mypokedex md LEFT JOIN pkm_pkmdata p ON p.nat_id = md.nat_id WHERE md.uid = ' . $trainer['uid']);
        $pokemon = array_fill(1, $count, ['is_owned' => 'n']);

        while($info = DB::fetch($query)) {
            ++$seen;
            $info['type']             = Obtain::TypeName($info['type'], $info['type_b']);
            $pokemon[$info['nat_id']] = $info;
        }

        break;
    case 'achievement':

        $query       = DB::query('SELECT ac.achv_id, ac.name_zh name, ac.cat_id, ac.description, mac.time_obtained FROM pkm_achievementdata ac LEFT JOIN pkm_myachievement mac ON mac.achv_id = ac.achv_id AND mac.uid = ' . $trainer['uid'] . ' ORDER BY cat_id ASC, achv_id ASC');
        $achievement = [];
        $catarr      = ['未分类', '图鉴登录'];

        while($info = DB::fetch($query)) {
            $info['cat_id'] = $catarr[$info['cat_id']];
            $achievement[]  = $info;
        }

        break;
    case 'inbox':

        /*
            Making the multipage
        */

        $count  = DB::result_first('SELECT COUNT(*) FROM pkm_myinbox WHERE uid_receiver = ' . $trainer['uid']);
        $multi  = Kit::MultiPage(8, $count);
        $unread = 0;

        $query   = DB::query('SELECT msg_id, title, content, time_sent, time_read, uid_sender FROM pkm_myinbox WHERE uid_receiver = ' . $trainer['uid'] . ' ORDER BY time_sent DESC LIMIT ' . $multi['start'] . ', ' . $multi['limit']);
        $message = [];

        while($info = DB::fetch($query)) {

            $info['time_sent'] = date('Y-m-d H:i:s', $info['time_sent']);
            $info['avatar']    = Obtain::Avatar($info['uid_sender']);

            $message[] = $info;

            if(!$info['time_read']) ++$unread;

        }

        if($unread) {

            DB::query('UPDATE pkm_trainerdata SET has_new_message = 0 WHERE uid = ' . $trainer['uid']);
            DB::query('UPDATE pkm_myinbox SET time_read = ' . $_SERVER['REQUEST_TIME'] . ' WHERE uid_receiver = ' . $trainer['uid']);

        }

        break;
    case 'inventory':

        $query   = DB::query('SELECT pkm_id, nickname, nat_id, level, gender, item_carrying, sprite_name FROM pkm_mypkm WHERE location < 7 ORDER BY location ASC');
        $pokemon = [];
        $iids    = [];

        while($info = DB::fetch($query)) {

            if($info['item_carrying']) $iids[] = $info['item_carrying'];

            $info['pkm_sprite']  = Obtain::Sprite('pokemon', 'gif', $info['sprite_name']);
            $info['pkmimgpathi'] = Obtain::Sprite('pokemon-icon', 'png', 'picon_' . $info['nat_id']);
            $info['carry_item_sprite'] = $info['item_carrying'] ? Obtain::Sprite('item', 'png', 'item_' . $info['item_carrying']) : '';
            $info['gender']      = Obtain::GenderSign($info['gender']);
            $pokemon[]           = $info;

        }


        $type  = (empty($_GET['type']) || $_GET['type'] < 1 && $_GET['type'] > 4) ? 0 : intval($_GET['type']);
        $query = DB::query('SELECT mi.item_id, mi.quantity, i.name_zh name, i.description, i.type, i.is_usable
                            FROM pkm_myitem mi
                            LEFT JOIN pkm_itemdata i ON i.item_id = mi.item_id
                            WHERE mi.uid = ' . $trainer['uid'] . ' AND mi.quantity > 0' .
            ($iids ? ' UNION ALL SELECT item_id, 0 quantity, name_zh name, description, type, is_usable FROM pkm_itemdata WHERE item_id IN (' . implode(',', $iids) . ')' : ''));
        $item  = [];
        $types = ['球类', '进化石', '携带道具', '药物'];

        while($info = DB::fetch($query)) {

            $info['carry_item_sprite']    = Obtain::Sprite('item', 'png', 'item_' . $info['item_id']);
            $item[$info['item_id']] = $info;

        }

        $item = json_encode($item);

        break;
}