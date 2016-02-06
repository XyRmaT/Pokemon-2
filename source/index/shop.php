<?php

$items = [];
$query = DB::query('SELECT item_id, name_zh name, description, price, stock, type
                    FROM pkm_itemdata
                    WHERE is_available = 1 AND trainer_level <= ' . $trainer['level'] . ' AND
                           (time_start = 0 AND time_end = 0 OR
                            time_start < ' . $_SERVER['REQUEST_TIME'] . ' AND time_end > ' . $_SERVER['REQUEST_TIME'] . ')
                    ORDER BY type, price');

while($info = DB::fetch($query)) {
    $info['item_sprite']     = Obtain::Sprite('item', 'png', 'item_' . $info['item_id']);
    $items[$info['item_id']] = $info;
}

$r['items'] = $items;