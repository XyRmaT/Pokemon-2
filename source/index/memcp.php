<?php

$_GET['section'] = !empty($_GET['section']) && in_array($_GET['section'], ['info', 'pokedex', 'achievement', 'inbox', 'setting', 'inventory'], TRUE) ? $_GET['section'] : 'party';
$r['section']    = $_GET['section'];

switch($_GET['section']) {
    case 'party':

        // Use for evolve: m.beauty, m.item_carrying, m.happiness, m.psn_value, m.ability, m.form
        $query   = DB::query('SELECT m.nat_id, m.pkm_id, m.gender, m.hp, m.exp, m.level, m.nature,
                                      m.nickname, m.form, m.eft_value, m.ind_value, m.moves_new, m.moves,
                                      m.sprite_name, m.item_captured, m.time_hatched, m.met_time, m.met_level,
                                      m.met_location, m.beauty, m.item_carrying, m.happiness, m.psn_value,
                                      m.form, m.uid_initial, m.status,
                                      a.name_zh ability,
                                      p.base_stat, p.type, p.type_b, p.exp_type, p.name_zh name, p.evolution_data,
                                      mb.username
                                FROM pkm_mypkm m
                                LEFT JOIN pkm_pkmdata p ON m.nat_id = p.nat_id AND m.nat_id != 0
                                LEFT JOIN pkm_abilitydata a ON m.ability = a.abi_id
                                LEFT JOIN pre_common_member mb ON mb.uid = m.uid_initial
                                WHERE m.location IN (1, 2, 3, 4, 5, 6) AND m.uid = ' . $trainer['uid'] . '
                                ORDER BY m.location');
        $pokemon = $move_ids = [];

        while($info = DB::fetch($query)) {
            switch($info['nat_id']) {
                case '0':

                    $info['pkm_sprite']    = Obtain::Sprite('egg', 'png', '');
                    $info['item_captured'] = Obtain::Sprite('item', 'png', 'item_' . $info['item_captured']);
                    $info['met_location']  = Obtain::MeetPlace($info['met_location']);
                    $info['maturity']      = min(floor(($_SERVER['REQUEST_TIME'] - $info['met_time']) / ($info['time_hatched'] - $info['met_time']) * 100), 90) + min(floor($info['exp'] / 100), 5) * 2;
                    $info['egg_phase']     = array_search(TRUE, [
                        $info['maturity'] < 27,
                        $info['maturity'] >= 27 && $info['maturity'] < 51,
                        $info['maturity'] >= 51 && $info['maturity'] < 93,
                        $info['maturity'] >= 93 && $info['maturity'] < 100,
                        $info['maturity'] >= 100
                    ]);

                    if($info['egg_phase'] === 4) Pokemon::Hatch($info['pkm_id']);

                    break;
                default:

                    $info = array_merge($info, Obtain::Stat($info['level'], $info['base_stat'], $info['ind_value'], $info['eft_value'], $info['nature'], $info['hp']));

                    $info['pkm_sprite']          = Obtain::Sprite('pokemon', 'gif', $info['sprite_name']);
                    $info['carry_item_sprite']   = $info['item_carrying'] ? Obtain::Sprite('item', 'png', 'item_' . $info['item_carrying']) : '';
                    $info['capture_item_sprite'] = Obtain::Sprite('item', 'png', 'item_' . $info['item_captured']);
                    $info['gender_sign']         = Obtain::GenderSign($info['gender']);
                    $info['types']               = Obtain::TypeName($info['type'], $info['type_b'], TRUE);
                    $info['met_location']        = Obtain::MeetPlace($info['met_location']);
                    $info['nature']              = Obtain::NatureName($info['nature']);
                    $info['status']              = Obtain::StatusIcon($info['status']);
                    $info['exp_this_level']      = Obtain::Exp($info['exp_type'], $info['level']);
                    $info['exp_required']        = Obtain::Exp($info['exp_type'], $info['level'] + 1) - $info['exp_this_level'];
                    $info['moves_new']           = $info['moves_new'] ? explode(',', $info['moves_new']) : [];
                    $info['moves']               = $info['moves'] ? unserialize($info['moves']) : [];
                    $info['happiness_phase']     = array_search(TRUE, [
                        $info['happiness'] < 50,
                        $info['happiness'] >= 50 && $info['happiness'] < 90,
                        $info['happiness'] >= 90 && $info['happiness'] < 150,
                        $info['happiness'] >= 150 && $info['happiness'] < 220,
                        $info['happiness'] >= 220
                    ]);

                    $move_ids = array_merge($move_ids, array_column($info['moves'], 'move_id'), $info['moves_new']);

                    break;
            }

            unset($info['eft_value'], $info['ind_value'], $info['base_stat'], $info['beauty'], $info['happiness'], $info['psn_value'], $info['time_hatched'], $info['evolution_data']);

            $pokemon[] = $info;
        }

        $moves = [];
        if($move_ids) {
            $query = DB::query('SELECT move_id, name_zh name, type, power, class FROM pkm_movedata WHERE move_id IN (' . implode(',', array_unique($move_ids)) . ')');
            while($info = DB::fetch($query))
                $moves[$info['move_id']] = [
                    'name'       => $info['name'],
                    'type'       => $info['type'],
                    'power'      => $info['power'],
                    'class_name' => Obtain::MoveClassName($info['class']),
                    'type_name'  => Obtain::TypeName($info['type'])
                ];
        }

        $r['pokemon'] = $pokemon;
        $r['moves']   = $moves;

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
        $query   = DB::query('SELECT DISTINCT md.nat_id, md.is_owned, p.name_zh name, p.type, p.type_b FROM pkm_mypokedex md LEFT JOIN pkm_pkmdata p ON p.nat_id = md.nat_id WHERE md.uid = ' . $trainer['uid']);
        $pokemon = array_fill(1, $count, ['is_owned' => 'n']);

        while($info = DB::fetch($query)) {
            ++$seen;
            $info['type']       = Obtain::TypeName($info['type'], $info['type_b']);
            $info['generation'] = intval(array_search(TRUE, array_map(function ($v) use ($info) {
                return $info['nat_id'] >= $v[1] && $info['nat_id'] < $v[2];
            }, $system['regions'])));

            $pokemon[$info['nat_id']] = $info;
        }

        $r['pokemon']           = $pokemon;
        $r['pokemon_total']     = count($pokemon);
        $r['dex_seen']          = $seen;
        $r['count_generations'] = array_count_values(array_column($pokemon, 'generation'));

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

            $info['pkm_sprite']        = Obtain::Sprite('pokemon', 'gif', $info['sprite_name']);
            $info['carry_item_sprite'] = $info['item_carrying'] ? Obtain::Sprite('item', 'png', 'item_' . $info['item_carrying']) : '';
            $info['gender']            = Obtain::GenderSign($info['gender']);
            $pokemon[]                 = $info;

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
            $info['carry_item_sprite'] = Obtain::Sprite('item', 'png', 'item_' . $info['item_id']);
            $item[$info['item_id']]    = $info;
        }

        break;
}