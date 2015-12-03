<?php

define('ACTIVATED', FALSE);

if(!ACTIVATED) exit('Access denied!');

$query = DB::query('SELECT pkm_id, moves, moves_new FROM pkm_mypkm');
while($info = DB::fetch($query)) {
    $moves     = @unserialize($info['moves']);
    $moves_new = @unserialize($info['moves_new']);
    if(empty($moves)) {
        DB::query('UPDATE pkm_mypkm SET moves = \'\' WHERE pkm_id = ' . $info['pkm_id']);
    } elseif(is_array($moves)) {
        foreach($moves as $key => $value)
            $moves[$key] = ['move_id' => $value[0], 'pp' => $value[1], 'pp_total' => $value[3], 'pp_up' => $value[4]];
        DB::query('UPDATE pkm_mypkm SET moves = \'' . serialize($moves) . '\' WHERE pkm_id = ' . $info['pkm_id']);
    }
    if(empty($moves_new)) {
        DB::query('UPDATE pkm_mypkm SET moves_new = \'\' WHERE pkm_id = ' . $info['pkm_id']);
    } elseif(is_array($moves_new)) {
        $arr = [];
        foreach($moves_new as $key => $value)
            $arr[] = $value[0];
        DB::query('UPDATE pkm_mypkm SET moves_new = \'' . implode(',', $arr) . '\' WHERE pkm_id = ' . $info['pkm_id']);
    }
}