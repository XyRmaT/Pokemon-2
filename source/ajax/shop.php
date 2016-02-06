<?php

switch($process) {
    case 'buy-item':

        $item_id  = isset($_GET['item_id']) ? intval($_GET['item_id']) : 0;
        $quantity = isset($_GET['quantity']) ? intval($_GET['quantity']) : 0;

        if($quantity <= 0) {
            $return['msg'] = Obtain::Text('enter_quantity');
        } elseif($item_id <= 0) {
            $return['msg'] = Obtain::Text('item_not_available');
        } else {

            $item = DB::fetch_first('SELECT price, name_zh name, stock
                                     FROM pkm_itemdata
                                     WHERE item_id = ' . $item_id . ' AND is_available = 1 AND
                                            trainer_level <= ' . $trainer['level'] . ' AND
                                            (time_start = 0 AND time_end = 0 OR
                                             time_start < ' . $_SERVER['REQUEST_TIME'] . ' AND time_end > ' . $_SERVER['REQUEST_TIME'] . ')');
            $cost = $item['price'] * $quantity;

            if(empty($item)) {
                $return['msg'] = Obtain::Text('item_not_available');
            } elseif($item['stock'] - $quantity < 0) {
                $return['msg'] = Obtain::Text('item_not_in_stock');
            } elseif($trainer['currency'] - $cost < 0) {
                $return['msg'] = Obtain::Text('unpaid');
            } else {

                if(!Trainer::Item('OBTAIN', $trainer['uid'], $item_id, $quantity)) {
                    $return['msg'] = Obtain::Text('bag_full');
                    break;
                }

                DB::query('UPDATE pkm_itemdata SET stock = stock - ' . $quantity . ', month_sale = month_sale + ' . $quantity . ' WHERE item_id = ' . $item_id);
                DB::query('UPDATE pkm_stat SET value = value + ' . $cost . ' WHERE `key` = \'shop_sale\'');

                Trainer::AddTemporaryStat('item_bought', $quantity);
                App::CreditsUpdate($trainer['uid'], -$cost);

                $return['msg'] = Obtain::Text('purchase_succeed');

            }
        }
        break;
}


?>