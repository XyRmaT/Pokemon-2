<?php

if(!$user['gm']) exit;

Kit::Library('class', ['obtain', 'pokemon']);

$query   = DB::query('SELECT m.pid, m.id, m.iv, m.gender, m.nature, m.move, m.level, m.exp, p.name, a.name abi, mb.username FROM pkm_mypkm m LEFT JOIN pkm_pkmdata p ON p.id = m.id AND m.id != 0 LEFT JOIN pkm_abilitydata a ON a.aid = m.abi LEFT JOIN pre_common_member mb ON mb.uid = m.uid ORDER BY m.pid');
$pokemon = [];
while($info = DB::fetch($query)) {
	$info['gender'] = Obtain::GenderSign($info['gender']);
	$info['nature'] = Obtain::NatureName($info['nature']);
	$info['move']   = unserialize($info['move']);
	foreach($info['move'] as $val) {
		$info['move'][] = $val[2];
		array_shift($info['move']);
	}
	$info['move'] = implode(', ', $info['move']);
	$pokemon[]    = $info;
}
$count = count($pokemon);
// Pokemon::CorrectAbility();