<?php

Kit::Library('class', ['battle', 'pokemon']);

$mapid = isset($_GET['map_id']) ? intval($_GET['map_id']) : 1;

/**
 * Fetch the available map data which met the criteria of:
 *      - In a certain time period
 *      ...
 */

$map = DB::fetch_first('SELECT map_id FROM pkm_mapdata
                        WHERE map_id = ' . $mapid . ' AND (time_start < ' . $_SERVER['REQUEST_TIME'] . ' AND time_end > ' . $_SERVER['REQUEST_TIME'] . ' OR time_end = 0 AND time_start < ' . $_SERVER['REQUEST_TIME'] . ')');


if(!$map) {

    Kit::ShowMessage('您要到哪里呀？');

} elseif(!$trainer['is_battling']) {

    $tmp   = [];
    $query = DB::query('SELECT id, levelmin, levelmax, rate
                         FROM pkm_encounterdata
                         WHERE map_id = ' . $mapid . ' AND timefrom < ' . $_SERVER['REQUEST_TIME'] . ' AND (timeto = 0 OR timeto > ' . $_SERVER['REQUEST_TIME'] . ') AND qty != 0');


    while($info = DB::fetch($query))
        $tmp[] = $info;


    if(!$tmp) {
        $return['msg'] = '一只精灵都没看到……';
        exit;
    }

    $i = 0;

    for(; ;) {

        $appearpkm = $tmp[array_rand($tmp)];

        if(rand(1, 100) <= $appearpkm['rate']) {
            $appearpkm['level'] = rand($appearpkm['levelmin'], $appearpkm['levelmax']);
            break;
        }

        if($i > 50) {
            $return['msg'] = '似乎有精灵的黑影闪过！';
            break;
        }

        ++$i;

    }

    DB::query('INSERT INTO pkm_battlefield (uid) VALUES (' . $trainer['uid'] . ') ON DUPLICATE KEY UPDATE weather = 0, trkroom = 0, gravity = 0');

    Battle::$pokemon[0] = [
        Pokemon::Generate($appearpkm['nat_id'], $trainer['uid'], [
            'met_location' => $map['map_id'],
            'met_level' => $appearpkm['level'],
            'wild'    => 1
        ]),
        Battle::GenerateBattleData()
    ];

    $query = DB::query('SELECT m.pkm_id, m.nat_id, m.nickname, m.gender, m.psn_value, m.ind_value, m.eft_value, m.nature, m.level, m.item_carrying, m.happiness, m.moves, m.ability, m.hp, m.status, m.sprite_name, p.base_stat, p.type, p.type_b FROM pkm_mypkm m LEFT JOIN pkm_pkmdata p ON m.nat_id = p.nat_id WHERE m.uid = ' . $trainer['uid'] . ' AND m.location IN (1, 2, 3, 4, 5, 6) AND m.nat_id != 0 ORDER BY m.location ASC');

    $hp = 0;
    $i  = 1;

    while($info = DB::fetch($query)) {

        $hp += $info['hp'];
        Battle::$pokemon[$i] = [$info, Battle::GenerateBattleData($info['pkm_id'])];

        ++$i;

    }

    if(empty(Battle::$pokemon) || $hp <= 0) {
        $return['msg'] = '您没有可以参战的精灵！';
        exit;
    }

    echo '<pre><img src="' . Obtain::Sprite('pokemon', 'png', Battle::$pokemon[0][0]['sprite_name']) . '"><img src="' . Obtain::Sprite('pokemon', 'png', Battle::$pokemon[1][0]['sprite_name']) . '"><br>Processed in ' . 0 . 'second(s), ' . 0 . ' queries.';
    print_r(Battle::$pokemon[0][0]);
    exit;

    Battle::$turn  = 'firstTurn';
    Battle::$field = ['000000000', '000000000'];
    Battle::$report .= '野生的' . Battle::$pokemon[0][0]['name'] . '出现了！<br>';

    unset($tmp, $query, $appearpkm);