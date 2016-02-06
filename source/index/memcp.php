<?php

$_GET['section'] = !empty($_GET['section']) && in_array($_GET['section'], ['info', 'pokedex', 'achievement', 'inbox', 'setting', 'inventory'], TRUE) ? $_GET['section'] : 'party';
$r['section']    = $_GET['section'];

switch($_GET['section']) {
    case 'party':

        $query   = DB::query('SELECT m.nat_id, m.pkm_id, m.gender, m.hp, m.exp, m.level, m.nature,
                                      m.nickname, m.form, m.eft_value, m.ind_value, m.new_moves, m.moves,
                                      m.sprite_name, m.item_captured, m.time_hatched, m.met_time, m.met_level,
                                      m.met_location, m.beauty, m.item_holding, m.happiness, m.psn_value,
                                      m.form, m.uid_initial, m.status, m.is_shiny,
                                      a.name_zh ability,
                                      p.base_stat, p.type, p.type_b, p.exp_type, p.name_zh name, p.evolution_data,
                                      mb.username
                                FROM pkm_mypkm m
                                LEFT JOIN pkm_pkmdata p ON m.nat_id = p.nat_id OR m.hatch_nat_id = p.nat_id
                                LEFT JOIN pkm_abilitydata a ON m.ability = a.abi_id
                                LEFT JOIN pre_common_member mb ON mb.uid = m.uid_initial
                                WHERE m.location IN (1, 2, 3, 4, 5, 6) AND m.uid = ' . $trainer['uid'] . '
                                ORDER BY m.location');
        $pokemon = $move_ids = [];

        while($info = DB::fetch($query)) {
            switch($info['nat_id']) {
                case 0:

                    $info['pkm_sprite']          = Obtain::Sprite('egg', 'png', '');
                    $info['capture_item_sprite'] = Obtain::Sprite('item', 'png', 'item_' . $info['item_captured']);
                    $info['met_location']        = Obtain::MeetPlace($info['met_location']);
                    $info['maturity']            = min(floor(($_SERVER['REQUEST_TIME'] - $info['met_time']) / ($info['time_hatched'] - $info['met_time']) * 100), 100) + min(floor($info['exp'] / 100), 5) * 2;
                    $info['egg_phase']           = array_search(TRUE, [
                        $info['maturity'] < 27,
                        $info['maturity'] >= 27 && $info['maturity'] < 51,
                        $info['maturity'] >= 51 && $info['maturity'] < 93,
                        $info['maturity'] >= 93 && $info['maturity'] < 100,
                        $info['maturity'] >= 100
                    ]);

                    if($info['egg_phase'] === 4) Pokemon::Hatch($info['pkm_id']);
                    $info['egg_phase'] = Obtain::Text('data_egg_phases', [], TRUE, FALSE, $info['egg_phase']);

                    unset($info['is_shiny'], $info['nature'], $info['ability'], $info['hp'], $info['new_moves'], $info['moves'], $info['maturity'], $info['time_hatched']);

                    break;
                default:

                    $info = array_merge($info, Obtain::Stat($info['level'], $info['base_stat'], $info['ind_value'], $info['eft_value'], $info['nature'], $info['hp']));

                    $info['pkm_sprite']          = Obtain::Sprite('pokemon', 'gif', $info['sprite_name']);
                    $info['hold_item_sprite']   = $info['item_holding'] ? Obtain::Sprite('item', 'png', 'item_' . $info['item_holding']) : '';
                    $info['capture_item_sprite'] = Obtain::Sprite('item', 'png', 'item_' . $info['item_captured']);
                    $info['gender_sign']         = Obtain::GenderSign($info['gender']);
                    $info['types']               = Obtain::TypeName($info['type'], $info['type_b'], TRUE);
                    $info['met_location']        = Obtain::MeetPlace($info['met_location']);
                    $info['nature']              = Obtain::NatureName($info['nature']);
                    $info['status']              = Obtain::StatusIcon($info['status']);
                    $info['exp_this_level']      = Obtain::Exp($info['exp_type'], $info['level']);
                    $info['exp_required']        = Obtain::Exp($info['exp_type'], $info['level'] + 1) - $info['exp_this_level'];
                    $info['new_moves']           = $info['new_moves'] ? explode(',', $info['new_moves']) : [];
                    $info['moves']               = $info['moves'] ? unserialize($info['moves']) : [];
                    $info['happiness_phase']     = array_search(TRUE, [
                        $info['happiness'] < 50,
                        $info['happiness'] >= 50 && $info['happiness'] < 90,
                        $info['happiness'] >= 90 && $info['happiness'] < 150,
                        $info['happiness'] >= 150 && $info['happiness'] < 220,
                        $info['happiness'] >= 220
                    ]);

                    $move_ids = array_merge($move_ids, array_column($info['moves'], 'move_id'), $info['new_moves']);

                    break;
            }

            unset($info['eft_value'], $info['ind_value'], $info['base_stat'], $info['beauty'], $info['happiness'], $info['psn_value'], $info['time_hatched'], $info['evolution_data']);

            $pokemon[] = $info;
        }

        $moves = [];
        if($move_ids) {
            $query = DB::query('SELECT move_id, name_zh name, type, power, class FROM pkm_movedata WHERE move_id IN (' . implode(',', array_filter(array_unique($move_ids), function ($v) {
                    return $v;
                })) . ')');
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

        $query    = DB::query('SELECT mi.msg_id, mi.title, mi.content, mi.time_sent, mi.time_read, mi.uid_sender, m.username
                              FROM pkm_myinbox mi LEFT JOIN pre_common_member m ON m.uid = mi.uid_sender
                              WHERE mi.uid_receiver = ' . $trainer['uid'] . '
                              ORDER BY mi.time_sent DESC');
        $messages = [];
        $unread   = 0;

        while($info = DB::fetch($query)) {
            $info['avatar']  = Obtain::Avatar($info['uid_sender']);
            $info['content'] = str_replace(['&', '<a '], ['&amp;', '<a target="_blank" '], $info['content']);
            $messages[]      = $info;
            if(!$info['time_read']) ++$unread;
        }

        if($unread) {
            DB::query('UPDATE pkm_trainerdata SET has_new_message = 0 WHERE uid = ' . $trainer['uid']);
            DB::query('UPDATE pkm_myinbox SET time_read = ' . $_SERVER['REQUEST_TIME'] . ' WHERE uid_receiver = ' . $trainer['uid']);
        }

        $r['messages']     = $messages;
        $r['unread_total'] = $unread;

        break;
    case 'inventory':

        $query = DB::query('SELECT ' . Kit::FetchFields([FIELDS_POKEMON_BASIC]) . ' FROM pkm_mypkm m WHERE location IN (' . LOCATION_PARTY . ') AND nat_id != 0 ORDER BY location');
        $party = [];
        $iids  = [];

        while($info = DB::fetch($query)) {

            if($info['item_holding']) $iids[] = $info['item_holding'];

            $info['pkm_sprite']          = Obtain::Sprite('pokemon', 'gif', $info['sprite_name']);
            $info['capture_item_sprite'] = Obtain::Sprite('item', 'png', 'item_' . $info['item_captured']);
            $info['hold_item_sprite']   = $info['item_holding'] ? Obtain::Sprite('item', 'png', 'item_' . $info['item_holding']) : '';
            $info['gender_sign']         = Obtain::GenderSign($info['gender']);
            $party[]                     = $info;

        }

        $query = DB::query(($iids ?
                            'SELECT item_id, 0 quantity, name_zh name, description, type, is_usable
                             FROM pkm_itemdata
                             WHERE item_id IN (' . implode(',', $iids) . ') UNION ALL ' : '') .
                            'SELECT mi.item_id, mi.quantity, i.name_zh name, i.description, i.type, i.is_usable
                            FROM pkm_myitem mi
                            LEFT JOIN pkm_itemdata i ON i.item_id = mi.item_id
                            WHERE mi.uid = ' . $trainer['uid'] . ' AND mi.quantity > 0 ');
        $items = [];

        while($info = DB::fetch($query)) {
            $info['item_sprite']     = Obtain::Sprite('item', 'png', 'item_' . $info['item_id']);
            $items[$info['item_id']] = $info;
        }

        $r['party'] = $party;
        $r['items'] = $items;
        $r['type']  = empty($_GET['type']) || $_GET['type'] < 1 && $_GET['type'] > 4 ? 0 : intval($_GET['type']);

        break;
}