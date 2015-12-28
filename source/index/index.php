<?php

// Random pokemon showcase
$count    = DB::result_first('SELECT COUNT(*) FROM pkm_mypkm WHERE nat_id != 0 AND (location IN (1, 2, 3, 4, 5, 6))');
$rand_pkm = DB::fetch_first('SELECT m.nickname, m.level, m.gender, m.sprite_name, m.uid, mb.username FROM pkm_mypkm m LEFT JOIN pre_common_member mb ON m.uid = mb.uid WHERE m.nat_id != 0 AND m.location IN (1, 2, 3, 4, 5, 6) LIMIT ' . rand(0, $count - 1) . ', 1');
if($rand_pkm) {
    $rand_pkm['pkm_sprite'] = Obtain::Sprite('pokemon', 'gif', $rand_pkm['sprite_name']);
    $rand_pkm['gender']     = Obtain::GenderSign($rand_pkm['gender']);
}

// Top 5 trainer ranking
$top_trainers = [];
$query        = DB::query('SELECT t.uid, t.level, t.exp, mb.username FROM pkm_trainerdata t LEFT JOIN pre_common_member mb ON t.uid = mb.uid ORDER BY exp DESC LIMIT 5');
while($info = DB::fetch($query)) {
    $info['avatar'] = Obtain::Avatar($info['uid']);
    $top_trainers[] = $info;
}

if(!empty($trainer['uid'])) {
    $party = [];
    $query = DB::query('SELECT nat_id FROM pkm_mypkm WHERE uid = ' . $trainer['uid'] . ' AND location IN (1, 2, 3, 4, 5, 6) AND nat_id != 0');
    while($info = DB::fetch($query))
        $party[] = $info['nat_id'];
    $smarty->assign('party', $party);
}

$world_stat = [
    'online_total'  => DB::result_first('SELECT COUNT(*) FROM pkm_trainerdata WHERE time_last_visit >= ' . ($_SERVER['REQUEST_TIME'] - 300)),
    'trainer_total' => DB::result_first('SELECT COUNT(*) FROM pkm_trainerdata'),
    'pokemon_total' => DB::result_first('SELECT COUNT(*) FROM pkm_mypkm WHERE nat_id > 0'),
    'shiny_total'   => DB::result_first('SELECT COUNT(*) FROM pkm_mypkm WHERE is_shiny = 1')
];

$smarty->assign('rand_pkm', $rand_pkm);
$smarty->assign('top_trainers', $top_trainers);
$smarty->assign('world_stat', $world_stat);