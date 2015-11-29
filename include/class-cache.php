<?php

/**
 * Basic parent cache class.
 * All rights reserved by Doduo (PokeUniv). This is NOT a free ware.
 */

/**
 * Main usage explain
 * - N/A
 * Data explain
 * - $extendpre: Use to define the name of child class
 * - $rfscache: Only sets with true when updating cache
 * - $param: Extra parameters for extended methods
 */
class cache {

	public static $extendpre = 'CC';
	public static $rfscache  = FALSE;
	public static $param     = [];

	public static function write($data, $filepath) {

		$writeFrag = '';

		foreach($data as $key => $value) {
			$writeFrag .= '$' . $key . ' = ' . var_export($value, TRUE) . ';' . "\n";
		}

		$writeIn = "<?php\n\n" .
				"/**\n" .
				" * This is a cache file generated by the cache class.\n" .
				" * Generated time: " . date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']) . "\n" .
				" */\n\n" .
				"{$writeFrag}\n" .
				"?>";

		$fp = fopen($filepath, 'w');

		if(in_array(FALSE, [flock($fp, LOCK_EX), fwrite($fp, $writeIn), flock($fp, LOCK_UN)])) {
			return FALSE;
			//exit('Caching failed! Please refresh the page!');
		}

		fclose($fp);
	}

	public static function css($filename, $varfilename = '') {

		$file    = '';
		$frmfile = TPLDIR . '/' . $filename[0] . '.css';
		$objfile = ROOTCACHE . '/css_' . TEMPLATEID . '_' . str_replace('/', '_', $filename[0]) . '.css';
		$varfile = TPLDIR . '/' . $varfilename . '.php';
		$frmtime = filemtime($frmfile);

		//if(file_exists($objfile) && $_SERVER['REQUEST_TIME'] - filemtime($objfile) >= 900)

		//	return $objfile;

		foreach($filename as $val) {

			$frmfile = TPLDIR . '/' . $val . '.css';

			file_exists($frmfile) && $file .= file_get_contents($frmfile);

		}

		if(!empty($varfilename) && file_exists($varfile) === TRUE) {

			include $varfile;

			$pattern = $replacement = [];

			foreach($cssvar as $name => $val) {

				$pattern[]     = '/%%' . $name . '%%/';
				$replacement[] = $val;

			}

			$file = preg_replace($pattern, $replacement, $file); // variables

		}

		$file = preg_replace('/\/\*{1,}[^\*]+\*{1,}\//', '', $file); // comments
		$file = preg_replace('/([\;\:])[\s]+/', '\\1', $file); // special signs (:;)
		$file = preg_replace('/[\s]{0,}([\{\}])[\s]+/', '\\1', $file); // all white space signs (\s)
		$file = preg_replace('/[\n\r]/', '', $file);
		$file = preg_replace('/;\s*\}/', '}', $file);

		$fp = fopen($objfile, 'w+');

		if(in_array(FALSE, [flock($fp, LOCK_EX), fwrite($fp, $file), flock($fp, LOCK_UN)]))

			return FALSE;

		return $objfile;
	}

	public static function refresh($actionArr) {
		self::$rfscache = TRUE;
		foreach($actionArr as $action) {
			if(self::load($action) === FALSE) {
				return FALSE;
			}
		}
		return TRUE;
	}

	public static function load($action, $extra = []) {
		self::$param = $extra;
		return call_user_func([self::$extendpre, '__' . $action]);
	}

}