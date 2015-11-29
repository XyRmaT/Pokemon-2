<?php

$_GET['mapid'] = 1;

if(!DB::result_first('SELECT uid FROM pkm_mapcoordinate WHERE uid = ' . $_G['uid'])) {
	DB::query('INSERT INTO pkm_mapcoordinate (uid, username, x, y, mpid, time) VALUES (' . $_G['uid'] . ', \'' . $_G['username'] . '\', 0, 10, ' . $_GET['mapid'] . ', ' . $_SERVER['REQUEST_TIME'] . ')');
}

include ROOT . '/data/map/map-' . $_GET['mapid'] . '.php';

$tilejs = [];

foreach($_tiles as $val) {

	$tilejs[] = '[' . implode(',', str_split($val)) . ']';

}

$tilejs = '[' . implode(',', $tilejs) . '];';

$query   = DB::query('SELECT uid, username, x, y FROM pkm_mapcoordinate WHERE mpid = ' . intval($_GET['mapid']));
$trainer = [];

while($info = DB::fetch($query)) {

	$info['x'] *= 16;
	$info['y'] *= 16;
	$info['id'] = ($info['uid'] == $_G['uid']) ? 'me' : 't' . $info['uid'];
	$trainer[]  = $info;

}

//var_dump(unserialize(gzinflate(file_get_contents(ROOTCACHE . '/battle/user-8'))));


?>