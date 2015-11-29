<?php

include dirname(__FILE__) . '/../include/class_cron.php';


/*
	Generate report for the shop
*/

$query	= DB::query('SELECT iid, mthsell FROM pkm_itemdata WHERE mthsell > 0 ORDER BY mthsell DESC');
$report	= '';

while($info = DB::fetch($query)) {

	$report .= implode("\t", $info) . PHP_EOL;
	
}

$report .= DB::result_first('SELECT shopsell FROM pkm_stat');

DB::query('UPDATE pkm_itemdata SET mthsell = 0');
DB::query('UPDATE pkm_stat SET shopsell = 0');

Cron::ReportWrite('shop', $report, 'Y-m', 'w+', $_SERVER['REQUEST_TIME'] - 3600);
Cron::LogInsert('Generate report from shop');


Cron::LogSave('monthly', 'Y');