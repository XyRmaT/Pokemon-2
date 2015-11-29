<?php

Kit::Library('class', array('pokemon', 'obtain'));

$trainer = $pokemon = $pokedex = $pokedexb = array();


// Trainer's rakning

$query = DB::query('SELECT t.level, t.uid, mb.username FROM pkm_trainerdata t LEFT JOIN pre_common_member mb ON mb.uid = t.uid ORDER BY t.exp DESC LIMIT 10');

while($info = DB::fetch($query)) {

	$info['avatar']	= Obtain::TrainerAvatar($info['uid'], 'small');
	$trainer[]		= $info;
	
}

// Pokemon's ranking

$query	= DB::query('SELECT m.id, m.nickname, m.level, m.gender, m.uid, mb.username FROM pkm_mypkm m LEFT JOIN pre_common_member mb ON mb.uid = m.uid ORDER BY m.level DESC, m.exp DESC LIMIT 10');

while($info = DB::fetch($query)) {

	$info['gender'] = Obtain::GenderSign($info['gender']);
	
	$pokemon[] = $info;
	
}

// Pokedex's ranking

$query = DB::query('SELECT COUNT(*) total, d.uid, mb.username FROM pkm_mypokedex d LEFT JOIN pre_common_member mb ON mb.uid = d.uid WHERE d.own IN (0, 1) GROUP BY d.uid ORDER BY total DESC LIMIT 10');

while($info = DB::fetch($query)) {

	$info['avatar']	= Obtain::TrainerAvatar($info['uid'], 'small');
	$pokedex[]		= $info;
	
}

// Pokedex's ranking

$query = DB::query('SELECT COUNT(*) total, d.uid, mb.username FROM pkm_mypokedex d LEFT JOIN pre_common_member mb ON mb.uid = d.uid WHERE d.own = 1 GROUP BY d.uid ORDER BY total DESC LIMIT 10');

while($info = DB::fetch($query)) {

	$info['avatar']	= Obtain::TrainerAvatar($info['uid'], 'small');
	$pokedexb[]		= $info;
	
}

