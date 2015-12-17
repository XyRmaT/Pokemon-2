<?php

Kit::Library('class', ['obtain']);

$query   = DB::query('SELECT m.pkm_id, m.nat_id, m.sprite_name, p.name_zh name FROM pkm_mypkm m LEFT JOIN pkm_pkmdata p ON p.nat_id = m.nat_id WHERE m.uid = 0 AND m.location = 9 AND m.nat_id != 0 UNION ALL SELECT pkm_id, nat_id, sprite_name, nickname FROM pkm_mypkm WHERE uid = 0 AND location = 9 AND nat_id = 0');
$pokemon = $egg = [];

while($info = DB::fetch($query)) {
	if($info['nat_id'] === '0') {
		$info['pkmimgpath'] = Obtain::Sprite('egg', 'png', '');
		$egg[]              = $info;
	} else {
		$info['pkmimgpath'] = Obtain::Sprite('pokemon', 'png', $info['sprite_name']);
		$pokemon[]          = $info;
	}
}

shuffle($pokemon);
shuffle($egg);

$pokemon = array_slice($pokemon, 0, 12);
$egg     = array_slice($egg, 0, 9);