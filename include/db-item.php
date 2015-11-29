<?php

class ItemDb {

	public static $pokemon   = [];
	public static $message   = '';
	public static $msgchoice = [
			'sucks' => ['%%name%%含泪注视着你……',
					'%%name%%差点吐了出来，幽怨地望着你……']
	];

	public static function __170() { # 力量之粉

		self::$pokemon['hpns'] -= min((self::$pokemon['hpns'] >= 200) ? 10 : 5, 255 - self::$pokemon['hpns']);
		self::$message = self::Message('sucks');

	}

	public static function Message($name) {

		shuffle(self::$msgchoice[$name]);

		$search      = ['%%name%%'];
		$replacement = [self::$pokemon['nickname']];

		return str_replace($search, $replacement, self::$msgchoice[$name][0]);

	}

	public static function __171() { # 力量之根

		self::$pokemon['hpns'] -= min((self::$pokemon['hpns'] >= 200) ? 15 : 10, 255 - self::$pokemon['hpns']);
		self::$message = self::Message('sucks');

	}

	public static function __186() { # 万能粉

		self::$pokemon['hpns'] -= min((self::$pokemon['hpns'] >= 200) ? 10 : 5, 255 - self::$pokemon['hpns']);
		self::$message = self::Message('sucks');

	}

	public static function __192() { # 复活草

		self::$pokemon['hpns'] -= min((self::$pokemon['hpns'] >= 200) ? 20 : 15, 255 - self::$pokemon['hpns']);
		self::$message = self::Message('sucks');

	}

}