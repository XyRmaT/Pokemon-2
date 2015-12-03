<?php

if(!$trainer['gm']) exit;

Kit::Library('class', ['obtain', 'pokemon']);

$query   = DB::query('SELECT m.pkm_id, m.nat_id, m.ind_value, m.gender, m.nature, m.moves, m.level, m.exp, p.name, a.name ability, mb.username FROM pkm_mypkm m LEFT JOIN pkm_pkmdata p ON p.nat_id = m.nat_id AND m.nat_id != 0 LEFT JOIN pkm_abilitydata a ON a.abi_id = m.ability LEFT JOIN pre_common_member mb ON mb.uid = m.uid ORDER BY m.pkm_id');
$pokemon = [];
while($info = DB::fetch($query)) {
	$info['gender'] = Obtain::GenderSign($info['gender']);
	$info['nature'] = Obtain::NatureName($info['nature']);
	$info['moves']   = unserialize($info['moves']);
	foreach($info['moves'] as $val) {
		$info['moves'][] = $val[2];
		array_shift($info['moves']);
	}
	$info['moves'] = implode(', ', $info['moves']);
	$pokemon[]    = $info;
}
$count = count($pokemon);
// Pokemon::CorrectAbility();