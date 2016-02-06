<?php

$query   = DB::query('SELECT m.pkm_id, m.nat_id, m.sprite_name, m.gender, m.level, p.name_zh name
                      FROM pkm_mypkm m
                      LEFT JOIN pkm_pkmdata p ON p.nat_id = m.nat_id
                      WHERE m.location = ' . LOCATION_SHELTER . ' AND m.nat_id != 0
                      UNION ALL
                      SELECT pkm_id, nat_id, sprite_name, gender, level, nickname name
                      FROM pkm_mypkm
                      WHERE location = ' . LOCATION_SHELTER . ' AND nat_id = 0');
$pokemon = $eggs = [];

while($info = DB::fetch($query)) {
    if(!$info['nat_id']) {
        $info['pkm_sprite'] = Obtain::Sprite('egg', 'png', '');
        $eggs[]             = $info;
    } else {
        $info['pkm_sprite']  = Obtain::Sprite('pokemon', 'gif', $info['sprite_name']);
        $info['gender_sign'] = Obtain::GenderSign($info['gender']);
        $pokemon[]           = $info;
    }
}

shuffle($pokemon);
shuffle($eggs);

$pokemon = array_slice($pokemon, 0, 10);
$eggs    = array_slice($eggs, 0, 8);

$r['pokemon'] = $pokemon;
$r['eggs']    = $eggs;