<?php

if($trainer['user_id'] == 8) error_reporting(E_ALL);

define('BATTLEMODE', 'WILD');

Kit::Library('class', ['obtain', 'battle', 'pokemon']);

$mid = !empty($_GET['move_id']) ? intval($_GET['move_id']) : 0;

$process = (!empty($process) && in_array($process, ['usemove', 'useitem', 'swappm']) && $trainer['is_battling'] === '1') ? $process : '';

switch($process) {
	case '':

		$return['msg'] = ($trainer['is_battling'] === '1') ? '??????' : '战斗已经结束！';

		goto BATTLEERROR;

		break;
	case 'usemove':

		$mid = !empty($_GET['move_id']) ? intval($_GET['move_id']) : 0;

		if($mid === 0) {

			$return['msg'] = '我不会这个技能额0A0';

			goto BATTLEERROR;

		}

		break;
}

Battle::$pokemon = Battle::LoadBattleData($trainer['user_id']);

$hptotal = 0;

foreach(Battle::$pokemon as $key => $val) {

	if($key > 0 && $key < 7) $hptotal += $val[0]['hp'];

}

if(empty(Battle::$pokemon) || $hptotal < 1) {

	$return['msg'] = '战斗结束！';

	$return['battle'] = ['end' => TRUE];

	goto BATTLEERROR;

} elseif(Battle::$pokemon[1][0]['hp'] < 1 && $process !== 'swappm') {

	$return['msg'] = '首发精灵已倒下，请更换精灵！';

	goto BATTLEERROR;

} elseif(Battle::$pokemon[1][0]['hp'] < 1 && $process === 'swappm') {

	Battle::$swapped = Battle::ReorderPokemon(intval($_GET['pkm_id']));

	Battle::$faintswap = !0;

	Battle::End();

	goto BATTLERETURN;

}


Battle::$swappid = (!empty($_GET['swappid'])) ? intval($_GET['swappid']) : 0;        // If user wants to switch pokemon, this exists
Battle::$field   = DB::fetch_first('SELECT weather, has_trickroom, has_gravity, current_turn FROM pkm_battlefield WHERE user_id = ' . $trainer['user_id']);

if(empty(Battle::$field)) {

	$return['msg'] = '没有战斗数据！';

	goto BATTLEERROR;

}


// Obtaining moves data for both opposite and self, also check if any moves is charging

MARKMOVECHARGE:

Battle::$move[0]        = Battle::$pokemon[0][0]['moves'];
Battle::$move[0]['key'] = Battle::$pokemon[0][1][6] ? Kit::ColumnSearch(Battle::$move[0], 0, Battle::$pokemon[0][1][6]) : array_rand(Battle::$move[0]);

if($process === 'usemove') {

	// If this is a consecutive attack, set the moves id to that one
	(Battle::$pokemon[1][1][2][43] !== FALSE) && ($mid = Battle::$pokemon[1][1][7]);

	Battle::$move[1]        = Battle::$pokemon[1][0]['moves'];
	Battle::$move[1]['key'] = Kit::ColumnSearch(Battle::$move[1], 0, Battle::$pokemon[1][1][6] ? Battle::$pokemon[1][1][6] : $mid);

}

$query = DB::query('SELECT move_id, name_zh name, type, class, power, acc, pp, prio, freq, ctrate, effect, battle_effect FROM pkm_movedata WHERE move_id = ' . Battle::$move[0][Battle::$move[0]['key']][0] . (($process === 'usemove') ? ' UNION ALL SELECT move_id, name_zh name, type, class, power, acc, pp, prio, freq, ctrate, effect, battle_effect FROM pkm_movedata WHERE move_id = ' . $mid : ''));
$i     = 0;

while($info = DB::fetch($query)) {

	Battle::$move[$i] = array_merge(Battle::$move[$i], $info);

	++$i;

}


Battle::Fight();

if(Battle::$pokemon[1][1][6]) {

	Battle::$report .= '<br>';

	goto MARKMOVECHARGE;

}

BATTLERETURN:

$return['battle'] = [
		'oppohp'     => Battle::$pokemon[0][0]['hp'],
		'oppomax_hp'  => Battle::$pokemon[0][0]['max_hp'],
		'oppostatus' => Obtain::StatusIcon(Battle::$pokemon[0][0]['status']),
		'selfhp'     => Battle::$pokemon[1][0]['hp'],
		'selfmax_hp'  => Battle::$pokemon[1][0]['max_hp'],
		'selfmove'   => Battle::$pokemon[1][0]['moves'],
		'selfstatus' => Obtain::StatusIcon(Battle::$pokemon[1][0]['status']),
		'end'        => Battle::$isend,
		'report'     => Battle::$report . '<br>'
];

$return['include'] = '';

if($process === 'useitem' && !empty($_GET['item_id'])) {

	/*
		Generate item info
	*/

	$tmp = '';

	foreach(Obtain::BagItem('(i.type = 1 AND i.is_usable = 0 OR i.type = 4 AND i.battle_effect != \'\' OR i.effect != \'\')', 'i.type ASC', 'GROUPED:type') as $val) {

		$tmp .= '<strong>' . Obtain::ItemClassName($val[0]['type']) . '</strong><ul>';

		foreach($val as $valb) {

			$tmp .= '<li data-item_id="' . $valb['item_id'] . '" title="' . $valb['name'] . '（余' . $valb['quantity'] . '个）：' . $valb['description'] . '"><img src="' . Obtain::Sprite('item', 'png', 'item_' . $valb['item_id']) . '"></li>';

		}

		$tmp .= '</ul><br clear="both">';

	}

	$return['include'] .= '$(\'#layer-item\').html(\'' . (!empty($tmp) ? $tmp : '你的背包空空如也！') . '\');';

} elseif($process === 'swappm' && Battle::$swapped) {

	$return['include'] = '$(\'#sbj-self\').html(\'' . Battle::$pokemon[1][0]['name'] . Battle::$pokemon[1][0]['gendersign'] . ' Lv. ' . Battle::$pokemon[1][0]['level'] . '<div class="bar"><div class="hp" style="width:' . ceil(Battle::$pokemon[1][0]['hp'] / Battle::$pokemon[1][0]['max_hp'] * 100) . '%"></div><div class="value">' . Battle::$pokemon[1][0]['hp'] . '/' . Battle::$pokemon[1][0]['max_hp'] . '</div></div><div class="sprite"><img src="' . Obtain::Sprite('pokemon', 'gif', Battle::$pokemon[1][0]['sprite_name'], 0, 1) . '"></div>\');';

	/*
		Generate pokemon info
	*/

	$tmp = '';

	foreach(Battle::$pokemon as $key => $val) {

		if($key < 2 || $key > 6 || $val[0]['hp'] < 1) continue;

		$tmp .= '<li data-pkm_id="' . $val[0]['pkm_id'] . '"><img src="' . ROOT_IMAGE . '/pokemon-icon/' . $val[0]['nat_id'] . '.png"> ' . $val[0]['name'] . ' Lv.' . $val[0]['level'] . '</li>';

	}

	$return['include'] .= '$(\'#layer-pokemon\').html(\'' . (!empty($tmp) ? '<ul>' . $tmp . '</ul>' : '没有可战斗的精灵。') . '\');';

} else {

	$return['include'] .= '$(\'#layer-item\').html($(\'#layer-item\').html());';

}


/*
$query = DB::query('SELECT battle_effect, move_id FROM pkm_movedata');
while($pkm = DB::fetch($query)) {
	DB::query('UPDATE pkm_movedata SET battle_effect = \'' . $pkm['battle_effect'] . '000\' WHERE move_id = ' . $pkm['move_id']);
}*/

BATTLEERROR:


?>