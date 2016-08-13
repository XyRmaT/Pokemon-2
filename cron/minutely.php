<?php

include __DIR__ . '/../include/class/cron.php';


/*
	Remove expire
*/

$minute = 10;
$query  = DB::query('DELETE FROM pkm_mapcoordinate WHERE time <= ' . ($_SERVER['REQUEST_TIME'] - 60 * $minute));

Cron::LogInsert('Delete expired coordinates');


Cron::LogSave('minutely', 'Y-m-d');
