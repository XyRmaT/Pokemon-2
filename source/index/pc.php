<?php

$allowed_sections = ['heal', 'trade', 'storage'];
$r['section']     = $section = !empty($_GET['section']) && in_array($_GET['section'], $allowed_sections, TRUE) ? $_GET['section'] : $allowed_sections[0];

switch($section) {
    default:

        $query = DB::query('SELECT m.pkm_id, m.nickname, m.gender, m.eft_value, m.level, m.nature, m.idv_value,
                                    m.hp, m.nat_id, m.time_pc_sent, m.sprite_name, p.base_stat
                            FROM pkm_mypkm m
                            LEFT JOIN pkm_pkmdata p ON m.nat_id = p.nat_id
                            WHERE m.location = ' . LOCATION_PCHEAL . ' AND m.user_id = ' . $trainer['user_id'] . ' ORDER BY m.time_pc_sent');
        $heal  = [];

        while($info = DB::fetch($query)) {
            $info                = array_merge($info, Obtain::Stat($info['level'], $info['base_stat'], $info['idv_value'], $info['eft_value'], 1, $info['hp']));
            $info['gender_sign'] = Obtain::GenderSign($info['gender']);
            $info['pkm_sprite']  = Obtain::Sprite('pokemon', 'gif', $info['sprite_name']);
            $info['remain_time'] = Obtain::HealRemainTime($info['time_pc_sent'], $info['max_hp'], $info['hp']);

            unset($info['base_stat'], $info['idv_value'], $info['eft_value']);

            $heal[] = $info;
        }

        $query = DB::query('SELECT m.pkm_id, m.nickname, m.sprite_name, m.hp, m.gender, m.idv_value, m.eft_value,
                                    m.item_captured, m.item_holding, m.level, p.nat_id, p.base_stat
                              FROM pkm_mypkm m
                              LEFT JOIN pkm_pkmdata p ON m.nat_id = p.nat_id
                              WHERE m.nat_id != 0 AND m.location IN (' . LOCATION_PARTY . ') AND user_id = ' . $trainer['user_id'] . ' ORDER BY m.location');
        $party = [];

        while($info = DB::fetch($query)) {
            $info                        = array_merge($info, Obtain::Stat($info['level'], $info['base_stat'], $info['idv_value'], $info['eft_value'], 1, $info['hp']));
            $info['gender_sign']         = Obtain::GenderSign($info['gender']);
            $info['pkm_sprite']          = Obtain::Sprite('pokemon', 'gif', $info['sprite_name']);
            $info['capture_item_sprite'] = Obtain::Sprite('item', 'png', 'item_' . $info['item_captured']);
            $info['hold_item_sprite']    = Obtain::Sprite('item', 'png', 'item_' . $info['item_holding']);

            unset($info['base_stat'], $info['idv_value'], $info['eft_value']);

            $party[] = $info;
        }

        $r['heal']  = $heal;
        $r['party'] = $party;

        break;
    case 'storage':

        $query  = DB::query('SELECT m.pkm_id, m.nat_id, m.location, m.nickname, m.level, m.gender, m.sprite_name,
                                      p.name_zh name, p.type, p.type_b, a.name_zh ability_name
                              FROM pkm_mypkm m
                              LEFT JOIN pkm_pkmdata p ON p.nat_id = m.nat_id
                              LEFT JOIN pkm_abilitydata a ON a.abi_id = m.ability
                              WHERE m.user_id = ' . $trainer['user_id'] . ' ORDER BY m.location');
        $boxes  = $party = [];
        $boxnum = $system['initial_box'] + $trainer['box_quantity'];

        for($i = 1; $i <= $boxnum; $i++)
            $boxes[$i + 100] = [];

        while($info = DB::fetch($query)) {

            if(!isset($boxes[$info['location']]) && $info['location'] > 6 || $info['location'] > 6 && $info['location'] < 101)
                continue;

            $info['gender'] = Obtain::GenderSign($info['gender']);
            $info['type']   = Obtain::TypeName($info['type'], $info['type_b']);

            if($info['location'] < 7) {
                $party[$info['pkm_id']] = $info;
            } else {
                $boxes[$info['location']][$info['pkm_id']] = $info;
            }

        }

        $r['boxes'] = $boxes;
        $r['party'] = $party;

        break;
    case 'trade':

        $query = DB::query('SELECT t.pkm_id, t.time_requested, t.user_id, t.target_user_id,
                                    m.nat_id, m.nickname, m.level, m.gender, m.nature, m.sprite_name,
                                    p.name_zh name, p.type, p.type_b,
                                    mo.pkm_id oppo_pkm_id, mo.nickname oppo_nickname, mo.level oppo_level,
                                    mo.gender oppo_gender, mo.nature oppo_nature, mo.sprite_name oppo_sprite_name,
                                    po.name_zh oppo_name, po.type oppo_type, po.type_b oppo_type_b,
                                    tn.trainer_name
                            FROM pkm_mytrade t
                            LEFT JOIN pkm_mypkm m ON m.pkm_id = t.pkm_id
                            LEFT JOIN pkm_mypkm mo ON mo.pkm_id = t.pkm_id_target
                            LEFT JOIN pkm_pkmdata p ON p.nat_id = m.nat_id
                            LEFT JOIN pkm_pkmdata po ON po.nat_id = mo.nat_id
                            LEFT JOIN pkm_trainerdata tn ON tn.user_id = t.user_id
                            WHERE t.user_id = ' . $trainer['user_id'] . ' OR t.target_user_id = ' . $trainer['user_id']);
        $sent  = $received = [];

        while($info = DB::fetch($query)) {

            $info['type']            = Obtain::TypeName($info['type'], $info['type_b']);
            $info['gender']          = Obtain::GenderSign($info['gender']);
            $info['nature']          = Obtain::NatureName($info['nature']);
            $info['pkm_sprite']      = !$info['nat_id'] ? Obtain::Sprite('egg', 'png', 0) : Obtain::Sprite('pokemon', 'gif', $info['sprite_name']);
            $info['oppo_type']       = Obtain::TypeName($info['oppo_type'], $info['oppo_type_b']);
            $info['oppo_gender']     = Obtain::GenderSign($info['oppo_gender']);
            $info['oppo_nature']     = Obtain::NatureName($info['oppo_nature']);
            $info['oppo_pkm_sprite'] = empty($info['oppo_id']) ? Obtain::Sprite('egg', 'png', 0) : Obtain::Sprite('pokemon', 'gif', $info['oppo_sprite_name']);

            if($info['user_id'] == $trainer['user_id']) $sent[] = $info;
            elseif($info['target_user_id'] == $trainer['user_id']) $received[] = $info;

        }

        $r['sent']     = $sent;
        $r['received'] = $received;

        break;
}