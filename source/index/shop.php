<?php


Kit::Library('class', ['obtain']);


//generateToken("is");
//Normal page display
//药物、战斗道具、携带道具、技能机器、球类、特殊道具

$type    = !empty($_GET['type']) ? intval($_GET['type']) : 1;
$page    = !empty($_GET['page']) ? intval($_GET['page']) : 1;
$start   = ($page - 1) * 10;
$limit   = 15;
$mthsell = DB::result_first('SELECT SUM(mthsell) sum FROM pkm_itemdata');
$item    = [];
$query   = DB::query('SELECT item_id, name_zh name, description, price, store, trnrlv, mthsell FROM pkm_itemdata WHERE sell = 1 AND type = ' . $type . ' AND trnrlv <= ' . $trainer['level'] . ' AND (timestt = 0 AND timefns = 0 OR timestt < ' . $_SERVER['REQUEST_TIME'] . ' AND timefns > ' . $_SERVER['REQUEST_TIME'] . ') ORDER BY price ASC LIMIT ' . $start . ', ' . $limit);

while($info = DB::fetch($query)) {

	$info['itemimgpath'] = Obtain::Sprite('item', 'png', 'item_' . $info['item_id']);
	$info['mthsellper']  = !empty($mthsell) ? round($info['mthsell'] / $mthsell * 100, 2) : '?';
	$item[]              = $info;

}

if(INAJAX) {

	$return['js'] = '$(\'.shop-list .item, .noitem\').remove();$(\'.shop-list\').append(\'';

	if(!empty($item)) {
		foreach($item as $val) {
			$return['js'] .= '<tr id="i' . $val['item_id'] . '" class="item">\' +
				\'<td><div style="background-image:url(' . $val['itemimgpath'] . ')"></div></td>\' + 
				\'<td>' . $val['name'] . '</td>\' + 
				\'<td>' . $val['description'] . '</td>\' +
				\'<td>' . $val['price'] . '</td>\' + 
				\'<td>' . $val['store'] . '</td>\' + 
				\'<td>' . $val['mthsell'] . '(' . $val['mthsellper'] . '%)</td>\' + 
				\'<td><button data-itemid="' . $val['item_id'] . '" data-name="' . $val['name'] . '">购买</button></td>\' +
				\'</tr>';
		}
	} else {
		$return['js'] .= '<tr class="noitem"><td colspan="7">什么都没卖噢=A=..请过阵子再来吧！</td></tr>';
	}

	$return['js'] .= '\');';

	exit(Kit::JsonConvert($return));

}

?>