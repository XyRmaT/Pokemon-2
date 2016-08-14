<?php

$query        = DB::query('SELECT nat_id, name_zh name, type, type_b, height, weight FROM pkm_pkmdata WHERE nat_id IN (' . implode(',', $system['starter']) . ')');
$r['pokemon'] = [];

while($info = DB::fetch($query)) {
    $info['pkm_sprite'] = Obtain::Sprite('pokemon', 'gif', 'pkm_' . $info['nat_id'] . '_0_0_0');
    $info['height'] /= 10;
    $info['weight'] /= 10;
    $r['pokemon'][] = $info;
}