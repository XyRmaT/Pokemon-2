<?php

switch($_GET['process']) {
	case 'itembuy':
	
		$iid = isset($_GET['iid']) ? intval($_GET['iid']) : 0;
		$num = isset($_GET['num']) ? intval($_GET['num']) : 0;

		if($iid === 0 || $num === 0) {

			$return['msg'] = '店长：别跟我开玩笑哟～';

		} else {

			$item	= DB::fetch_first('SELECT price, name, store FROM pkm_itemdata WHERE iid = ' . $iid . ' AND sell = 1 AND trnrlv <= ' . $user['level'] . ' AND (timestt = 0 AND timefns = 0 OR NOW() > timestt AND NOW() < timefns) LIMIT 1');
			$cost	= $item['price'] * $num;

			if(empty($item)) {
			
				$return['msg'] = '您无法购买这个道具哟～';
				
			} elseif($item['store'] - $num < 0) {
			
				$return['msg'] = '十分抱歉！我们店里的库存不足了！请隔段时间再光临鄙店！';
				
			} elseif($user['money'] - $cost < 0) {

				$return['msg'] = '没钱？这不伤感情么！';

			} else {

				$bagnum = DB::result_first('SELECT num FROM pkm_myitem WHERE uid = ' . $user['uid'] . ' AND iid = ' . $iid);

				if($bagnum + $num >= $SYS['bagperlimit']) {

					$return['msg'] = '唔背包都这么鼓了塞哪里？';

				} else {

					Trainer::AddTemporaryStat('itembuy', $num);

					DB::query('UPDATE pkm_itemdata SET store = store - ' . $num . ', mthsell = mthsell + ' . $num . ' WHERE iid = ' . $iid);
					DB::query('UPDATE pkm_stat SET shopsell = shopsell + ' . $cost);
					DB::query('UPDATE pre_common_member_count SET ' . $SYS['moneyext'] . ' = ' . $SYS['moneyext'] . '- ' . $cost . ' WHERE uid = ' . $user['uid']);

					if(empty($bagnum))

						DB::query('INSERT INTO pkm_myitem (uid, iid, num) VALUES (' . $user['uid'] . ', ' . $iid . ', ' . $num . ')');

					else

						DB::query('UPDATE pkm_myitem SET num = num + ' . $num . ' WHERE iid = ' . $iid . ' AND uid = ' . $user['uid']);

					$return['msg']	= '这是您的' . $item['name'] . '*' . $num . '，共耗费' . $cost . $SYS['moneyname'] . '。谢谢光临！';
					$return['js']	= '$(\'#i' . $iid . ' td\').eq(4).html(\'' . ($item['store'] - $num) . '\');$(\'#money\').html(' . ($user['money'] - $cost) . ');';
				}
			}
		}
	break;
}


?>