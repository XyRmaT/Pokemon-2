<?php

Kit::Library('class', ['obtain']);

$query   = DB::query('SELECT m.pid, m.id, m.imgname, p.name FROM pkm_mypkm m LEFT JOIN pkm_pkmdata p ON p.id = m.id WHERE m.uid = 0 AND m.place = 9 AND m.id != 0 UNION ALL SELECT pid, id, imgname, nickname FROM pkm_mypkm WHERE uid = 0 AND place = 9 AND id = 0');
$pokemon = $egg = [];

while($info = DB::fetch($query)) {

	if($info['id'] === '0') {

		$info['pkmimgpath'] = Obtain::Sprite('egg', 'png', '');
		$egg[]              = $info;

	} else {

		$info['pkmimgpath'] = Obtain::Sprite('pokemon', 'png', $info['imgname']);
		$pokemon[]          = $info;

	}

}

shuffle($pokemon);
shuffle($egg);

$pokemon = array_slice($pokemon, 0, 12);
$egg     = array_slice($egg, 0, 9);

?>