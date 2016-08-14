<?php

Kit::Library('class', ['pokemon', 'obtain']);

$topTrainer = $pokemon = $pokedex = $pokedexb = [];


// Trainer's rakning
$query = DB::query('SELECT level, user_id, trainer_name FROM pkm_trainerdata ORDER BY exp DESC LIMIT 10');
while($info = DB::fetch($query)) {
    $info['avatar'] = Obtain::Avatar($info['user_id'], 'small');
    $topTrainer[]   = $info;
}

// Pokemon's ranking
$query = DB::query('SELECT m.nat_id, m.nickname, m.level, m.gender, m.user_id, t.trainer_name FROM pkm_mypkm m LEFT JOIN pkm_trainerdata t ON t.user_id = m.user_id ORDER BY m.level DESC, m.exp DESC LIMIT 10');
while($info = DB::fetch($query)) {
    $info['gender'] = Obtain::GenderSign($info['gender']);
    $pokemon[]      = $info;
}

// Pokedex's ranking
$query = DB::query('SELECT COUNT(*) total, d.user_id, t.trainer_name FROM pkm_mypokedex d LEFT JOIN pkm_trainerdata t ON t.user_id = d.user_id WHERE d.is_owned IN (0, 1) GROUP BY d.user_id ORDER BY total DESC LIMIT 10');
while($info = DB::fetch($query)) {
    $info['avatar'] = Obtain::Avatar($info['user_id'], 'small');
    $pokedex[]      = $info;
}

// Pokedex's ranking
$query = DB::query('SELECT COUNT(*) total, d.user_id, t.trainer_name FROM pkm_mypokedex d LEFT JOIN pkm_trainerdata t ON t.user_id = d.user_id WHERE d.is_owned = 1 GROUP BY d.user_id ORDER BY total DESC LIMIT 10');
while($info = DB::fetch($query)) {
    $info['avatar'] = Obtain::Avatar($info['user_id'], 'small');
    $pokedexb[]     = $info;
}

