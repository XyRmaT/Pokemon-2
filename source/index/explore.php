<?php

$_GET['mapid'] = 1;

if(!DB::result_first('SELECT user_id FROM pkm_mapcoordinate WHERE user_id = ' . $trainer['user_id'])) {
	DB::query('INSERT INTO pkm_mapcoordinate (user_id, trainer_name, coord_x, coord_y, map_id, time_last) VALUES (' . $trainer['user_id'] . ', \'' . $trainer['trainer_name'] . '\', 0, 10, ' . $_GET['mapid'] . ', ' . $_SERVER['REQUEST_TIME'] . ')');
}

include ROOT . '/data/map/map-' . $_GET['mapid'] . '.php';

$tilejs = [];

foreach($_tiles as $val)
	$tilejs[] = '[' . implode(',', str_split($val)) . ']';

$tilejs = '[' . implode(',', $tilejs) . '];';

$query         = DB::query('SELECT user_id, trainer_name, coord_x, coord_y FROM pkm_mapcoordinate WHERE map_id = ' . intval($_GET['mapid']));
$onlineTrainer = [];

while($info = DB::fetch($query)) {

	$info['x'] *= 16;
	$info['y'] *= 16;
	$info['nat_id']      = ($info['user_id'] == $trainer['user_id']) ? 'me' : 't' . $info['user_id'];
	$onlineTrainer[] = $info;

}

//var_dump(unserialize(gzinflate(file_get_contents(ROOT_CACHE . '/battle/user-8'))));


?>