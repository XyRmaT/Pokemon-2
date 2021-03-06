<?php

include __DIR__ . '/../include/class/common.php';
include __DIR__ . '/../include/class/cron.php';

App::Initialize();

/*
	Generate report for the shop
*/

$query  = DB::query('SELECT item_id, month_sale FROM pkm_itemdata WHERE month_sale > 0 ORDER BY month_sale DESC');
$report = '';

while($info = DB::fetch($query)) {

    $report .= implode("\t", $info) . PHP_EOL;

}

$report .= DB::result_first('SELECT shopsell FROM pkm_stat');

DB::query('UPDATE pkm_itemdata SET month_sale = 0');
DB::query('UPDATE pkm_stat SET shopsell = 0');

Cron::ReportWrite('shop', $report, 'Y-m', 'w+', time() - 3600);
Cron::LogInsert('Generate report from shop');


Cron::LogSave('monthly', 'Y');