<?php

class Deprecated {

    public static function ArrayIconv($from, $to, &$array) {
        if(is_array($array)) {
            foreach($array as &$k)
                self::ArrayIconv($from, $to, $k);
        } else {
            $array = iconv($from, $to, $array);
        }
        return $array;
    }

    public static function MultiPage($limit, $count = 0, $ulproperty = '', $tag = 'a') {

        $pagenum    = !empty($_GET['pagenum']) ? max(intval($_GET['pagenum']), 1) : 1;
        $start      = ($pagenum - 1) * $limit;
        $maxpagenum = ($count === 0) ? 9999 : ceil($count / $limit);

        $multipage = '<ul class="flt-r mp"' . ($ulproperty ? ' ' . $ulproperty : '') . '>' . (($pagenum > 1) ? '<li data-pagenum="' . max($pagenum - 1, 1) . '">&lt;&lt;</li>' : '');

        for($i = max($pagenum - 5, 1), $j = min($pagenum + 5, $maxpagenum); $i <= $j; $i++)
            $multipage .= '<li data-pagenum="' . $i . '"' . (($i == $pagenum) ? ' class="cur"' : '') . '>' . $i . '</li>';

        $multipage .= (($pagenum < $maxpagenum) ? '<li data-pagenum="' . min($pagenum + 1, $maxpagenum) . '">&gt;&gt;</li>' : '') . '</ul>';

        return [
            'start'   => $start,
            'limit'   => $limit,
            'display' => $multipage
        ];

    }
}