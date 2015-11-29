<?php

if($SYS['switch'] === 0) exit;

class Cron {

	public static $log = '';

	public static function LogInsert($logpart) {

		self::$log .= $logpart . '. Affected rows: ' . DB::affected_rows() . '. ' . date('Y-m-d H:i:s') . PHP_EOL;
		
	}

	public static function LogSave($id, $format) {
	
		$fp = fopen(ROOT . '/log/cron-' . $id . '-' . date($format) . '.log', 'a');
		
		fwrite($fp, self::$log);
		
		fclose($fp);
		
	}
	
	public static function ReportWrite($id, $content, $format, $mode, $timestamp = 0) {
	
		$fp = fopen(ROOT . '/report/' . $id . '-' . ($timestamp ? date($format, $timestamp) : date($format)) . '.log', $mode);
	
		fwrite($fp, $content);
		
		fclose($fp);
		
	}
		
}