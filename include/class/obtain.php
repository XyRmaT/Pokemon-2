<?php

class Obtain {

    public static function BagItem ($condition = '', $orderby = '', $mode = '') {

        global $trainer;

        $condition = ($condition !== '') ? ' AND ' . $condition : '';
        $orderby   = ($orderby !== '') ? ' ORDER BY ' . $orderby : '';
        $mode      = ($mode !== '') ? explode(':', $mode) : '';
        $query     = DB::query('SELECT mi.item_id, mi.quantity, i.name_zh name, i.description, i.type
                                 FROM pkm_myitem mi
                                 LEFT JOIN pkm_itemdata i ON i.item_id = mi.item_id
                                 WHERE mi.user_id = ' . $trainer['user_id'] . $condition . $orderby);
        $item      = [];
        while ($info = DB::fetch($query)) {
            if ($mode !== '') $item[$info[$mode[1]]][] = $info;
            else                $item[] = $info;
        }
        return $item;
    }



    public static function DaycareInfo ($sent_time) {
        return [
            'cost'          => (floor(($_SERVER['REQUEST_TIME'] - $sent_time) / 3600) + 1) * 10,
            'exp_increased' => floor(($_SERVER['REQUEST_TIME'] - $sent_time) / 12)
        ];
    }





}