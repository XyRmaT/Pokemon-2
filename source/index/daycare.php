<?php

Kit::Library('class', ['pokemon']);

$query   = DB::query('SELECT m.pkm_id, m.level, m.nickname, m.nat_id, m.time_daycare_sent, m.time_egg_checked,
                              m.gender, m.initial_user_id, m.sprite_name, m.item_holding, m.item_captured, m.has_egg,
                              p.egg_group, p.egg_group_b, p.name_zh
                      FROM pkm_mypkm m
                      LEFT JOIN pkm_pkmdata p ON m.nat_id = p.nat_id
                      WHERE location = ' . LOCATION_DAYCARE . ' AND user_id = ' . $trainer['user_id'] . ' ORDER BY m.time_daycare_sent LIMIT 2');
$pokemon = [];

while ($info = DB::fetch($query)) {

    $info['egg_group_name']      = Obtain::EggGroupName($info['egg_group'], $info['egg_group_b']);
    $info['gender_sign']         = Obtain::GenderSign($info['gender']);
    $info['pkm_sprite']          = Obtain::Sprite('pokemon', 'gif', $info['sprite_name']);
    $info['capture_item_sprite'] = Obtain::Sprite('item', 'png', 'item_' . $info['item_captured']);
    $info['hold_item_sprite']    = Obtain::Sprite('item', 'png', 'item_' . $info['item_holding']);
    $info                        = array_merge($info, Obtain::DaycareInfo($info['time_daycare_sent']));

    $pokemon[] = $info;

}

$pkm_count  = count($pokemon);
$egg_chance = 0;

if ($pkm_count === 2) {

    // If one of the following criteria is met, then there's no possibility of having offspring:
    //  - One of them has an egg group of Undiscovered
    //  - Both of them have an egg group of Ditto
    //  - Both of them are same gender, and none of each has an egg group of Ditto
    //  - Their egg groups don't match
    $egg_is_possible = !(in_array(EGGGROUP_UNDISCOVERED, [$pokemon[0]['egg_group'], $pokemon[1]['egg_group']]) ||
        $pokemon[0]['egg_group'] == EGGGROUP_DITTO && $pokemon[1]['egg_group'] == EGGGROUP_DITTO ||
        !in_array(EGGGROUP_DITTO, [$pokemon[0]['egg_group'], $pokemon[1]['egg_group']]) &&
        ($pokemon[0]['gender'] == $pokemon[1]['gender'] ||
            !in_array($pokemon[0]['egg_group'], [$pokemon[1]['egg_group'], $pokemon[1]['egg_group_b']]) ||
            $pokemon[0]['egg_group_b'] &&
            !in_array($pokemon[0]['egg_group_b'], [$pokemon[1]['egg_group'], $pokemon[1]['egg_group_b']])
        ));

    // Deciding the max boundary $egg_chance within 100 that this couple will get an offspring
    if ($egg_is_possible) {
        if ($pokemon[0]['nat_id'] === $pokemon[1]['nat_id'])
            $egg_chance = $pokemon[0]['initial_user_id'] === $pokemon[1]['initial_user_id'] ? 50 : 70;
        else
            $egg_chance = $pokemon[0]['initial_user_id'] === $pokemon[1]['initial_user_id'] ? 20 : 50;
    }

    if (!empty($pokemon[0]['has_egg']) && !empty($pokemon[1]['has_egg'])) goto FETCH_PARTY;

    if ($egg_is_possible && (empty($pokemon[0]['has_egg']) || empty($pokemon[1]['has_egg']))) {

        $stamp       = $system['daycare_check_hour'] * 60 * 60;
        $check_times = floor(($_SERVER['REQUEST_TIME'] - ($pokemon[0]['time_egg_checked'] ? $pokemon[0]['time_egg_checked'] : $pokemon[0]['time_daycare_sent'])) / $stamp);

        if ($check_times > 0)
            DB::query('UPDATE pkm_mypkm SET time_egg_checked = ' . $_SERVER['REQUEST_TIME'] . ' WHERE pkm_id IN (' . $pokemon[0]['pkm_id'] . ', ' . $pokemon[1]['pkm_id'] . ')');

        for ($i = 0; $i < $check_times; $i++) {
            if (rand(0, 100) <= $egg_chance) {
                $pokemon[0]['has_egg'] = $pokemon[1]['has_egg'] = 1;
                DB::query('UPDATE pkm_mypkm SET has_egg = 1 WHERE pkm_id IN (' . $pokemon[0]['pkm_id'] . ', ' . $pokemon[1]['pkm_id'] . ')');
                break;
            }
        }

    }

}

FETCH_PARTY:

$query = DB::query('SELECT m.nat_id, m.nickname, m.pkm_id, m.sprite_name, m.level, m.gender, m.item_captured, m.item_holding, p.egg_group, p.egg_group_b FROM pkm_mypkm m LEFT JOIN pkm_pkmdata p ON m.nat_id = p.nat_id WHERE m.location IN (1, 2, 3, 4, 5, 6) AND m.nat_id != 0 AND m.user_id = ' . $trainer['user_id'] . ' LIMIT 6');
$party = [];

while ($info = DB::fetch($query)) {

    $info['egg_group']        = Obtain::EggGroupName($info['egg_group'], $info['egg_group_b']);
    $info['pkm_sprite']       = Obtain::Sprite('pokemon', 'gif', $info['sprite_name']);
    $info['hold_item_sprite'] = Obtain::Sprite('item', 'png', 'item_' . $info['item_holding']);;
    $info['capture_item_sprite'] = Obtain::Sprite('item', 'png', 'item_' . $info['item_captured']);
    $info['gender_sign']         = Obtain::GenderSign($info['gender']);

    $party[] = $info;

}

$r += ['pokemon' => $pokemon, 'party' => $party, 'egg_chance' => $egg_chance];