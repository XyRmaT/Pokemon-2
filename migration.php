<?php

define('ACTIVATED', FALSE);

if(!ACTIVATED) exit('Access denied!');

$query = DB::query('SELECT pkm_id, moves, new_moves FROM pkm_mypkm');
while($info = DB::fetch($query)) {
    $moves     = @unserialize($info['moves']);
    $new_moves = @unserialize($info['new_moves']);
    if(empty($moves)) {
        DB::query('UPDATE pkm_mypkm SET moves = \'\' WHERE pkm_id = ' . $info['pkm_id']);
    } elseif(is_array($moves)) {
        foreach($moves as $key => $value)
            $moves[$key] = ['move_id' => $value[0], 'pp' => $value[1], 'pp_total' => $value[3], 'pp_up' => $value[4]];
        DB::query('UPDATE pkm_mypkm SET moves = \'' . serialize($moves) . '\' WHERE pkm_id = ' . $info['pkm_id']);
    }
    if(empty($new_moves)) {
        DB::query('UPDATE pkm_mypkm SET new_moves = \'\' WHERE pkm_id = ' . $info['pkm_id']);
    } elseif(is_array($new_moves)) {
        $arr = [];
        foreach($new_moves as $key => $value)
            $arr[] = $value[0];
        DB::query('UPDATE pkm_mypkm SET new_moves = \'' . implode(',', $arr) . '\' WHERE pkm_id = ' . $info['pkm_id']);
    }
}