<?php

include dirname(__FILE__) . '/../include/class_cron.php';


/*
	Remove expire
*/

$minute = 10;
$query  = DB::query('DELETE FROM pkm_mapcoordinate WHERE time <= ' . ($_SERVER['REQUEST_TIME'] - 60 * $minute));

Cron::LogInsert('Delete expired coordinates');


Cron::LogSave('minutely', 'Y-m-d');
