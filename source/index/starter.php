<?php

Kit::Library('class', ['obtain']);

$starter = [1, 4, 7, 152, 155, 158, 252, 255, 258, 387, 390, 393, 495, 498, 501];
$query   = DB::query('SELECT nat_id, name_zh name, type, type_b, height, weight FROM pkm_pkmdata WHERE nat_id IN (' . implode(',', $starter) . ')');
$pokemon = [];

while($info = DB::fetch($query)) {

	$info['type']       = Obtain::TypeName($info['type'], $info['type_b']);
	$info['pkm_sprite'] = Obtain::Sprite('pokemon', 'gif', 'pkm_' . $info['nat_id'] . '_0_0_0');
	$info['height'] /= 10;
	$info['weight'] /= 10;
	$pokemon[] = $info;

}


?>