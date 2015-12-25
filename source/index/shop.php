<?php


Kit::Library('class', ['obtain']);


//generateToken("is");
//Normal page display
//药物、战斗道具、携带道具、技能机器、球类、特殊道具

$type    = !empty($_GET['type']) ? intval($_GET['type']) : 1;
$page    = !empty($_GET['page']) ? intval($_GET['page']) : 1;
$start   = ($page - 1) * 10;
$limit   = 15;
$mthsell = DB::result_first('SELECT SUM(month_sale) sum FROM pkm_itemdata');
$item    = [];
$query   = DB::query('SELECT item_id, name_zh name, description, price, stock, trainer_level, month_sale FROM pkm_itemdata WHERE is_available = 1 AND type = ' . $type . ' AND trainer_level <= ' . $trainer['level'] . ' AND (time_start = 0 AND time_end = 0 OR time_start < ' . $_SERVER['REQUEST_TIME'] . ' AND time_end > ' . $_SERVER['REQUEST_TIME'] . ') ORDER BY price ASC LIMIT ' . $start . ', ' . $limit);

while($info = DB::fetch($query)) {

	$info['carry_item_sprite'] = Obtain::Sprite('item', 'png', 'item_' . $info['item_id']);
	$info['mthsellper']  = !empty($mthsell) ? round($info['month_sale'] / $mthsell * 100, 2) : '?';
	$item[]              = $info;

}

if(INAJAX) {

	$return['js'] = '$(\'.shop-list .item, .noitem\').remove();$(\'.shop-list\').append(\'';

	if(!empty($item)) {
		foreach($item as $val) {
			$return['js'] .= '<tr id="i' . $val['item_id'] . '" class="item">\' +
				\'<td><div style="background-image:url(' . $val['carry_item_sprite'] . ')"></div></td>\' +
				\'<td>' . $val['name'] . '</td>\' + 
				\'<td>' . $val['description'] . '</td>\' +
				\'<td>' . $val['price'] . '</td>\' + 
				\'<td>' . $val['stock'] . '</td>\' +
				\'<td>' . $val['month_sale'] . '(' . $val['mthsellper'] . '%)</td>\' +
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