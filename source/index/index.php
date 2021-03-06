<?php

// Random pokemon showcase
$count    = DB::result_first('SELECT COUNT(*) FROM pkm_mypkm WHERE nat_id != 0 AND (location IN (1, 2, 3, 4, 5, 6))');
$rand_pkm = DB::fetch_first('SELECT m.nickname, m.level, m.gender, m.sprite_name, m.user_id, t.trainer_name 
                                FROM pkm_mypkm m 
                                LEFT JOIN pkm_trainerdata t ON m.user_id = t.user_id 
                                WHERE m.nat_id != 0 AND m.location IN (1, 2, 3, 4, 5, 6) 
                                LIMIT ' . rand(0, $count - 1) . ', 1');
if($rand_pkm) {
    $rand_pkm['pkm_sprite'] = Obtain::Sprite('pokemon', 'gif', $rand_pkm['sprite_name']);
    $rand_pkm['gender']     = Obtain::GenderSign($rand_pkm['gender']);
}

// Top 5 trainer ranking
$top_trainers = [];
$query        = DB::query('SELECT user_id, level, exp, trainer_name FROM pkm_trainerdata ORDER BY exp DESC LIMIT 5');
while($info = DB::fetch($query)) {
    $info['avatar'] = Obtain::Avatar($info['user_id']);
    $top_trainers[] = $info;
}

if(!empty($trainer['user_id'])) {
    $party = [];
    $query = DB::query('SELECT nat_id FROM pkm_mypkm WHERE user_id = ' . $trainer['user_id'] . ' AND location IN (1, 2, 3, 4, 5, 6) AND nat_id != 0');
    while($info = DB::fetch($query))
        $party[] = $info['nat_id'];
    $r['party'] = $party;
}

$world_stat = [
    'online_total'  => DB::result_first('SELECT COUNT(*) FROM pkm_trainerdata WHERE time_last_visit >= ' . ($_SERVER['REQUEST_TIME'] - 300)),
    'trainer_total' => DB::result_first('SELECT COUNT(*) FROM pkm_trainerdata'),
    'pokemon_total' => DB::result_first('SELECT COUNT(*) FROM pkm_mypkm WHERE nat_id > 0'),
    'shiny_total'   => DB::result_first('SELECT COUNT(*) FROM pkm_mypkm WHERE is_shiny = 1')
];

$r['top_trainers'] = $top_trainers;
$r['world_stat']   = $world_stat;
$r['rand_pkm']     = $rand_pkm;