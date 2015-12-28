<?php

switch($process) {
    case 'itembuy':

        $iid = isset($_GET['item_id']) ? intval($_GET['item_id']) : 0;
        $num = isset($_GET['quantity']) ? intval($_GET['quantity']) : 0;

        if($iid === 0 || $num === 0) {

            $return['msg'] = '店长：别跟我开玩笑哟～';

        } else {

            $item = DB::fetch_first('SELECT price, name_zh name, stock FROM pkm_itemdata WHERE item_id = ' . $iid . ' AND is_available = 1 AND trainer_level <= ' . $trainer['level'] . ' AND (time_start = 0 AND time_end = 0 OR NOW() > time_start AND NOW() < time_end) LIMIT 1');
            $cost = $item['price'] * $num;

            if(empty($item)) {

                $return['msg'] = '您无法购买这个道具哟～';

            } elseif($item['stock'] - $num < 0) {

                $return['msg'] = '十分抱歉！我们店里的库存不足了！请隔段时间再光临鄙店！';

            } elseif($trainer['currency'] - $cost < 0) {

                $return['msg'] = '没钱？这不伤感情么！';

            } else {

                $bagnum = DB::result_first('SELECT quantity FROM pkm_myitem WHERE uid = ' . $trainer['uid'] . ' AND item_id = ' . $iid);

                if($bagnum + $num >= $system['per_item_limit']) {

                    $return['msg'] = '唔背包都这么鼓了塞哪里？';

                } else {

                    Trainer::AddTemporaryStat('itembuy', $num);

                    DB::query('UPDATE pkm_itemdata SET stock = stock - ' . $num . ', month_sale = month_sale + ' . $num . ' WHERE item_id = ' . $iid);
                    DB::query('UPDATE pkm_stat SET shopsell = shopsell + ' . $cost);

                    App::CreditsUpdate($trainer['uid'], -$cost);

                    if(empty($bagnum))
                        DB::query('INSERT INTO pkm_myitem (uid, item_id, quantity) VALUES (' . $trainer['uid'] . ', ' . $iid . ', ' . $num . ')');

                    else

                        DB::query('UPDATE pkm_myitem SET quantity = quantity + ' . $num . ' WHERE item_id = ' . $iid . ' AND uid = ' . $trainer['uid']);

                    $return['msg'] = '这是您的' . $item['name'] . '*' . $num . '，共耗费' . $cost . $system['currency_name'] . '。谢谢光临！';
                    $return['js']  = '$(\'#i' . $iid . ' td\').eq(4).html(\'' . ($item['stock'] - $num) . '\');$(\'#currency\').html(' . ($trainer['currency'] - $cost) . ');';
                }
            }
        }
        break;
}


?>