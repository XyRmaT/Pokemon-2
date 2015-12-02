<?php

if($trainer['uid'] == 8) error_reporting(E_ALL);

define('BATTLEMODE', 'WILD');

Kit::Library('class', ['obtain', 'battle', 'pokemon']);

$mid = !empty($_GET['mid']) ? intval($_GET['mid']) : 0;

$_GET['process'] = (!empty($_GET['process']) && in_array($_GET['process'], ['usemove', 'useitem', 'swappm']) && $trainer['inbtl'] === '1') ? $_GET['process'] : '';

switch($_GET['process']) {
	case '':

		$return['msg'] = ($trainer['inbtl'] === '1') ? '??????' : '战斗已经结束！';

		goto BATTLEERROR;

		break;
	case 'usemove':

		$mid = !empty($_GET['mid']) ? intval($_GET['mid']) : 0;

		if($mid === 0) {

			$return['msg'] = '我不会这个技能额0A0';

			goto BATTLEERROR;

		}

		break;
}

Battle::$pokemon = Battle::LoadBattleData($trainer['uid']);

$hptotal = 0;

foreach(Battle::$pokemon as $key => $val) {

	if($key > 0 && $key < 7) $hptotal += $val[0]['hp'];

}

if(empty(Battle::$pokemon) || $hptotal < 1) {

	$return['msg'] = '战斗结束！';

	$return['battle'] = ['end' => TRUE];

	goto BATTLEERROR;

} elseif(Battle::$pokemon[1][0]['hp'] < 1 && $_GET['process'] !== 'swappm') {

	$return['msg'] = '首发精灵已倒下，请更换精灵！';

	goto BATTLEERROR;

} elseif(Battle::$pokemon[1][0]['hp'] < 1 && $_GET['process'] === 'swappm') {

	Battle::$swapped = Battle::ReorderPokemon(intval($_GET['pid']));

	Battle::$faintswap = !0;

	Battle::End();

	goto BATTLERETURN;

}


Battle::$swappid = (!empty($_GET['swappid'])) ? intval($_GET['swappid']) : 0;        // If user wants to switch pokemon, this exists
Battle::$field   = DB::fetch_first('SELECT weather, trkroom, gravity, turn FROM pkm_battlefield WHERE uid = ' . $trainer['uid']);

if(empty(Battle::$field)) {

	$return['msg'] = '没有战斗数据！';

	goto BATTLEERROR;

}


// Obtaining move data for both opposite and self, also check if any moves is charging

MARKMOVECHARGE:

Battle::$move[0]        = Battle::$pokemon[0][0]['move'];
Battle::$move[0]['key'] = Battle::$pokemon[0][1][6] ? Kit::ColumnSearch(Battle::$move[0], 0, Battle::$pokemon[0][1][6]) : array_rand(Battle::$move[0]);

if($_GET['process'] === 'usemove') {

	// If this is a consecutive attack, set the move id to that one
	(Battle::$pokemon[1][1][2][43] !== FALSE) && ($mid = Battle::$pokemon[1][1][7]);

	Battle::$move[1]        = Battle::$pokemon[1][0]['move'];
	Battle::$move[1]['key'] = Kit::ColumnSearch(Battle::$move[1], 0, Battle::$pokemon[1][1][6] ? Battle::$pokemon[1][1][6] : $mid);

}

$query = DB::query('SELECT mid, name_zh, type, class, power, acc, pp, prio, freq, ctrate, effect, btlefct FROM pkm_movedata WHERE mid = ' . Battle::$move[0][Battle::$move[0]['key']][0] . (($_GET['process'] === 'usemove') ? ' UNION ALL SELECT mid, name_zh, type, class, power, acc, pp, prio, freq, ctrate, effect, btlefct FROM pkm_movedata WHERE mid = ' . $mid : ''));
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
		'oppomaxhp'  => Battle::$pokemon[0][0]['maxhp'],
		'oppostatus' => Obtain::StatusIcon(Battle::$pokemon[0][0]['status']),
		'selfhp'     => Battle::$pokemon[1][0]['hp'],
		'selfmaxhp'  => Battle::$pokemon[1][0]['maxhp'],
		'selfmove'   => Battle::$pokemon[1][0]['move'],
		'selfstatus' => Obtain::StatusIcon(Battle::$pokemon[1][0]['status']),
		'end'        => Battle::$isend,
		'report'     => Battle::$report . '<br>'
];

$return['js'] = '';

if($_GET['process'] === 'useitem' && !empty($_GET['iid'])) {

	/*
		Generate item info
	*/

	$tmp = '';

	foreach(Obtain::BagItem('(i.type = 1 AND i.usable = 0 OR i.type = 4 AND i.btlefct != \'\' OR i.effect != \'\')', 'i.type ASC', 'GROUPED:type') as $val) {

		$tmp .= '<strong>' . Obtain::ItemClassName($val[0]['type']) . '</strong><ul>';

		foreach($val as $valb) {

			$tmp .= '<li data-iid="' . $valb['iid'] . '" title="' . $valb['name'] . '（余' . $valb['num'] . '个）：' . $valb['dscptn'] . '"><img src="' . Obtain::Sprite('item', 'png', 'item_' . $valb['iid']) . '"></li>';

		}

		$tmp .= '</ul><br clear="both">';

	}

	$return['js'] .= '$(\'#lyr-item\').html(\'' . (!empty($tmp) ? $tmp : '你的背包空空如也！') . '\');';

} elseif($_GET['process'] === 'swappm' && Battle::$swapped) {

	$return['js'] = '$(\'#sbj-self\').html(\'' . Battle::$pokemon[1][0]['name'] . Battle::$pokemon[1][0]['gendersign'] . ' Lv. ' . Battle::$pokemon[1][0]['level'] . '<div class="bar"><div class="hp" style="width:' . ceil(Battle::$pokemon[1][0]['hp'] / Battle::$pokemon[1][0]['maxhp'] * 100) . '%"></div><div class="value">' . Battle::$pokemon[1][0]['hp'] . '/' . Battle::$pokemon[1][0]['maxhp'] . '</div></div><div class="sprite"><img src="' . Obtain::Sprite('pokemon', 'png', Battle::$pokemon[1][0]['imgname'], 0, 1) . '"></div>\');';

	/*
		Generate pokemon info
	*/

	$tmp = '';

	foreach(Battle::$pokemon as $key => $val) {

		if($key < 2 || $key > 6 || $val[0]['hp'] < 1) continue;

		$tmp .= '<li data-pid="' . $val[0]['pid'] . '"><img src="' . ROOTIMG . '/pokemon-icon/' . $val[0]['id'] . '.png"> ' . $val[0]['name'] . ' Lv.' . $val[0]['level'] . '</li>';

	}

	$return['js'] .= '$(\'#lyr-pokemon\').html(\'' . (!empty($tmp) ? '<ul>' . $tmp . '</ul>' : '没有可战斗的精灵。') . '\');';

} else {

	$return['js'] .= '$(\'#lyr-item\').html($(\'#lyr-item\').html());';

}


/*
$query = DB::query('SELECT btlefct, mid FROM pkm_movedata');
while($pkm = DB::fetch($query)) {
	DB::query('UPDATE pkm_movedata SET btlefct = \'' . $pkm['btlefct'] . '000\' WHERE mid = ' . $pkm['mid']);
}*/

BATTLEERROR:


?>