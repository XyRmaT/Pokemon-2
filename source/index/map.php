<?php

$_GET['mapid'] = 1;

if(!DB::result_first('SELECT uid FROM pkm_mapcoordinate WHERE uid = ' . $trainer['uid'])) {
	DB::query('INSERT INTO pkm_mapcoordinate (uid, username, x, y, map_id, time) VALUES (' . $trainer['uid'] . ', \'' . $trainer['username'] . '\', 0, 10, ' . $_GET['mapid'] . ', ' . $_SERVER['REQUEST_TIME'] . ')');
}

include ROOT . '/data/map/map-' . $_GET['mapid'] . '.php';

$tilejs = [];

foreach($_tiles as $val) {

	$tilejs[] = '[' . implode(',', str_split($val)) . ']';

}

$tilejs = '[' . implode(',', $tilejs) . '];';

$query         = DB::query('SELECT uid, username, x, y FROM pkm_mapcoordinate WHERE map_id = ' . intval($_GET['mapid']));
$onlineTrainer = [];

while($info = DB::fetch($query)) {

	$info['x'] *= 16;
	$info['y'] *= 16;
	$info['id']      = ($info['uid'] == $trainer['uid']) ? 'me' : 't' . $info['uid'];
	$onlineTrainer[] = $info;

}

//var_dump(unserialize(gzinflate(file_get_contents(ROOTCACHE . '/battle/user-8'))));


?>