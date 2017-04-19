<?php

class General {

    public static function combineParams ($default_param, $param) : array {
        foreach ($default_param as $key => $val)
            if (!isset($param[$key])) $param[$key] = $val;
        return $param;
    }

    // TODO
    public static function getTimeFrame () {
        return 1;
    }


    /**
     * Fetch a certain text from the language pack.
     * If it's not a dataset and it's an array, then randomize one.
     * @param string $key name of the text
     * @param array $args arguments that will be replacing placeholders using vsprintf
     * @param bool $is_data if is a dataset, then it doesn't need to check if it's an array or not
     * @param bool $add_linebreak
     * @param int $data_index
     * @return string
     */
    public static function getText (string $key, array $args = [], bool $is_data = FALSE, bool $add_linebreak = FALSE, int $data_index = 0) {

        if (!isset($GLOBALS['lang'][$key]))
            return $GLOBALS['lang']['inoccupied_text'];

        $text = $GLOBALS['lang'][$key];
        if ($is_data)
            $text = $text[$data_index];
        if (!$is_data && is_array($text))
            $text = $text[array_rand($text)];
        if ($args)
            $text = vsprintf($text, $args);

        return $text . ($add_linebreak ? '<br>' : '');

    }


    public static function fuzzyHas ($needle, $haystack, bool $strict = TRUE) {
        if (gettype($haystack) === 'array')
            return array_search($needle, $haystack, $strict);
        else
            return $strict ? $needle === $haystack : $needle == $haystack;
    }
}