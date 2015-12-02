<?php

switch($_GET['process']) {

	case 'open':

		/*
			公测学习装置---- 1%
			大师球X1---1%
			公测安闲铃----3%
			内测学习装置----5%
			内测安闲铃----5%
			内测探测仪----5%
			进化石随机X1---15%
			福袋----15%
			精灵球X2~10---25%
			药X2~10----25%
		*/

		if($trainer['lbagnum'] <= 0) {

			$return['msg'] = '哪来的福袋？';

			break;

		}

		$gifts     = [
				'public-item-208'    => range(1, 1),
				'test-item-4'        => range(2, 2),
				'public-item-211'    => range(3, 5),
				'test-item-208'      => range(6, 10),
				'test-item-211'      => range(11, 15),
				'test-item-212'      => range(16, 20),
				'test-randitem-evol' => range(21, 35),
				'test-again'         => range(36, 50),
				'test-randitem-ball' => range(51, 75),
				'test-randitem-medi' => range(76, 100)
		];
		$available = [
				'public-item-208' => 1,
				'test-item-4'     => 2,
				'public-item-211' => 1,
				'test-item-208'   => 5,
				'test-item-211'   => 5,
				'test-item-212'   => 5
		];

		$balls  = [1, 2, 3, 6, 7, 8, 9, 10, 11, 12, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23];
		$evols  = [27, 28, 29, 30, 31, 32, 33, 34, 35, 207, 45, 48, 49, 52, 131, 132, 198, 199, 200, 201, 202, 203, 204, 205, 206];
		$medis  = [165, 166, 167, 168, 169];
		$over   = [];
		$own    = [];
		$looped = 0;

		RAND:

		if($looped > 50) {

			$return['msg'] = '请重试！';

			break;

		}

		$rand = rand(1, 100);

		foreach($gifts as $key => $val) {

			if(in_array($rand, $val, !0)) {

				if(!empty($available[$key]) && (
								(!empty($ever[$key]) ||
										($ever[$key] = DB::result_first('SELECT COUNT(*) FROM pkm_luckybaglog WHERE value = \'' . $key . '\'')) >= $available[$key]) ||
								(!empty($own[$key]) ||
										($own[$key] = DB::result_first('SELECT COUNT(*) FROM pkm_luckybaglog WHERE value = \'' . $key . '\' AND uid = ' . $trainer['uid'])) > 0)) && ++$looped
				)

					goto RAND;

				$param = explode('-', $key);

				switch($param[1]) {
					case 'item':

						$num = 1;
						$iid = $param[2];

						break;
					case 'randitem':

						$num = ($param[2] === 'evol') ? 1 : rand(2, 10);

						switch($param[2]) {
							case 'ball':
								$iid = $balls[array_rand($balls)];
								break;
							case 'evol':
								$iid = $evols[array_rand($evols)];
								break;
							case 'medi':
								$iid = $medis[array_rand($medis)];
								break;
						}

						break;
				}

				//if($param[1] !== 'again')

				//	DB::query('UPDATE pkm_trainerdata SET lbagnum = lbagnum - 1 WHERE uid = ' . $trainer['uid']);

				if($param[1] === 'again') {

					$return['msg'] = '获得了额外1个福袋！' . '！还剩下' . $trainer['lbagnum'] . '个福袋！';


				} else {

					$item = DB::result_first('SELECT name FROM pkm_itemdata WHERE iid = ' . $iid);

					$return['msg'] = '获得了' . $num . '个' . (($param[0] === 'public') ? '公测' : '内测') . $item . '！' . (($trainer['lbagnum'] - 1 > 0) ? '还剩下' . ($trainer['lbagnum'] - 1) . '个福袋！' : '没有福袋了！');
					$return['num'] = $trainer['lbagnum'] - 1;

					if($param[0] === 'test') {

						$count = DB::result_first('SELECT COUNT(*) FROM pkm_myitem WHERE iid = ' . $iid . ' AND uid = ' . $trainer['uid']);

						if($count < 1)

							DB::query('INSERT INTO pkm_myitem (iid, uid, num) VALUES (' . $iid . ', ' . $trainer['uid'] . ', ' . $num . ')');

						else

							DB::query('UPDATE pkm_myitem SET num = num + ' . $num . ' WHERE iid = ' . $iid . ' AND uid = ' . $trainer['uid']);

					}

					DB::query('UPDATE pkm_trainerdata SET lbagnum = ' . $return['num'] . ' WHERE uid = ' . $trainer['uid']);

				}

				DB::query('INSERT INTO pkm_luckybaglog (uid, value, time, ip) VALUES (' . $trainer['uid'] . ', \'' . $key . '\', ' . $_SERVER['REQUEST_TIME'] . ', \'' . $user['ip'] . '\')');

				break 2;

			}

		}

		break;

}

?>