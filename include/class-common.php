<?php


class Kit {

	/*
		Author: mail@theopensource.com (01-Feb-2006 03:34)
		$array: the array you want to sort
		$by: the associative array name that is one level deep
		example: name
		$order: ASC or DESC
		$type: num or str
	*/

	public static function ColumnSort($array, $by, $order, $type) {

		$sortby   = 'sort' . $by;
		$firstval = current($array);
		$vals     = array_keys($firstval);

		foreach($vals as $init) {

			$keyname  = 'sort' . $init;
			$$keyname = [];
		}

		foreach($array as $key => $row) {

			foreach($vals as $names) {

				$keyname    = 'sort' . $names;
				$test       = [];
				$test[$key] = $row[$names];
				$$keyname   = array_merge($$keyname, $test);

			}

		}

		array_multisort($$sortby, ($order === 'DESC') ? SORT_DESC : SORT_ASC, ($type === 'num') ? SORT_NUMERIC : SORT_STRING, $array);

		return $array;
	}


	public static function ArrayIconv($from, $to, &$array) {

		if(is_array($array)) {

			foreach($array as &$k) {

				self::ArrayIconv($from, $to, $k);

			}

		} else {

			$array = iconv($from, $to, $array);

		}

		return $array;

	}


	public static function JsonConvert($array) {

		//return json_encode(self::ArrayIconv('gbk', 'utf-8//IGNORE', $array));
		return json_encode($array);

	}


	public static function MultiPage($limit, $count = 0, $ulproperty = '', $tag = 'a') {

		$pagenum    = !empty($_GET['pagenum']) ? max(intval($_GET['pagenum']), 1) : 1;
		$start      = ($pagenum - 1) * $limit;
		$maxpagenum = ($count === 0) ? 9999 : ceil($count / $limit);

		$multipage = '<ul class="flt-r mp"' . ($ulproperty ? ' ' . $ulproperty : '') . '>' . (($pagenum > 1) ? '<li data-pagenum="' . max($pagenum - 1, 1) . '">&lt;&lt;</li>' : '');

		for($i = max($pagenum - 5, 1), $j = min($pagenum + 5, $maxpagenum); $i <= $j; $i++) {

			$multipage .= '<li data-pagenum="' . $i . '"' . (($i == $pagenum) ? ' class="cur"' : '') . '>' . $i . '</li>';

		}

		$multipage .= (($pagenum < $maxpagenum) ? '<li data-pagenum="' . min($pagenum + 1, $maxpagenum) . '">&gt;&gt;</li>' : '') . '</ul>';

		return [
				'start'   => $start,
				'limit'   => $limit,
				'display' => $multipage
		];

	}


	public static function ColumnSearch($array, $column, $value) {

		if(is_array($array)) {
			foreach($array as $key => $val) {

				if($val[$column] == $value) {

					return $key;

				}

			}
		}

		return FALSE;

	}

	public static function Library($type, $file) {

		foreach($file as $val) {

			if($type === 'class' || $type === 'db') {

				require_once ROOT . '/include/' . $type . '-' . $val . '.php';

			}

		}

		return TRUE;

	}

	public static function Memory($size) {

		$unit = ['b', 'kb', 'mb', 'gb', 'tb', 'pb'];

		return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];

	}

	public static function SendMessage($title, $content, $from, $to) {

		DB::query('INSERT INTO pkm_myinbox (title, content, receiver, dateline) VALUES (\'' . $title . '\', \'' . $content . '\', ' . $from . ', ' . $to . ', ' . $_SERVER['REQUEST_TIME'] . ')');
		DB::query('UPDATE pkm_trainerdata SET newmsg = 1 WHERE uid = ' . $to);

	}

	public static function TableSelect($name, $prefix = FALSE) {

		/*$table = array(
			'pkm_mypkm'		=> 't', 
			'pkm_pkmdata'	=> 'p', 
			'pre'
			
	
		foreach($name as $key => $val) {
		
			if(!is_array($val))*/

	}

	public static function NumberFormat($num) {

		return ($num > 999999) ? round($num / 1000000) . 'm' : (($num > 999) ? round($num / 1000) . 'k' : $num);

	}

}