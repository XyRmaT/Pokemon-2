<?php


define('INPOKE', TRUE);
define('INAJAX', (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest' || !empty($_GET['aaa']) && $_GET['aaa'] === '1') ? TRUE : FALSE);
define('ROOT', dirname(__FILE__));
define('YEAR', date('Y', $_SERVER['REQUEST_TIME']));
define('TEMPLATEID', 1);
define('ROOTIMG', './source_img');
define('TPLDIR', './source_tpl');
define('ROOTCACHE', './cache');
define('ROOTREL', '');

include_once ROOT . '/include/class-common.php';
App::Initialize();
error_reporting(E_ALL);

// If the system is closed and it is not GM visiting, display the error message
if($system['system_switch'] === 0 && $trainer['uid'] != 8)
    exit($system['close_reason']);


// Load up the essential libraries

Kit::Library('class', ['trainer', 'obtain']);


// Set up some global variables, also keep the minified CSS file up to date

//$SYS            = array_merge($SYS, DB::fetch_first('SELECT shopsell, shopopc FROM pkm_stat'));
$index       = isset($_GET['index']) && in_array($_GET['index'], ['my', 'pc', 'copyright', 'starter', 'shop', 'daycare', 'index', 'battle', 'map', 'tempview', 'tempaward', 'ranking', 'shelter']) ? $_GET['index'] : 'index';
$path['css'] = Cache::Css(['index/stylesheet', 'css/jquery-ui-1.10.3.custom'], 'index/cssvar');


// Change the default timezone to +8
date_default_timezone_set('Asia/Shanghai');

if(!empty($user['uid'])) {

    $trainer = DB::fetch_first('SELECT t.uid, t.trainer_id, t.exp, t.level, t.has_starter, t.box_quantity, t.time_happiness_checked, t.is_battling, t.has_new_message,
                              ts.uid exist, ts.pkm_evolved, ts.item_bought, ts.pkm_traded, ts.pkm_hatched
                              FROM pkm_trainerdata t
                              LEFT JOIN pkm_trainerstat ts
                              ON ts.uid = t.uid
                              WHERE t.uid = ' . $user['uid']);

    if(!$trainer) {
        Trainer::Generate($trainer['uid']);
    } else {

        $trainer['extcredit'] = DB::fetch_first('SELECT ' . $system['currency_field'] . ', ' . $system['exp_field'] . ' FROM pre_common_member_count WHERE uid = ' . $trainer['uid']);
        $trainer['gm']        = in_array($trainer['uid'], explode(',', $system['admins']));
        $trainer['money']     = $trainer['extcredit'][$system['currency_field']];
        $trainer['avatar']    = Obtain::Avatar($trainer['uid']);

        // Add EXP gained from forum posts to the party, and clear the counter

        if(!empty($trainer['sttchk']) && $trainer['extcredit'][$system['exp_field']] > 0) {

            DB::query('UPDATE pkm_mypkm SET exp = exp + ' . $trainer['extcredit'][$system['exp_field']] . ' WHERE uid = ' . $trainer['uid'] . ' AND location IN (1, 2, 3, 4, 5, 6)');
            App::CreditsUpdate($trainer['uid'], 0, 'EXP', TRUE);

            $trainer['extcredit'][$system['exp_field']] = 0;

        }

    }

    // Set up the old & new stat counter

    if(!$trainer['exist']) {

        DB::query('INSERT INTO pkm_trainerstat (uid) VALUES (' . $trainer['uid'] . ')');

        $trainer['stat']['old'] = $trainer['stat']['new'] = [
            'pmevolve' => 0,
            'itembuy'  => 0,
            'pmhatch'  => 0
        ];

    } else {

        $trainer['stat']['old'] = $trainer['stat']['new'] = [
            'pmevolve' => $trainer['pmevolve'],
            'itembuy'  => $trainer['itembuy'],
            'pmhatch'  => $trainer['pmhatch']
        ];

    }

}


// Each half an hour randomly add 1~2 happiness to the party, and update the timer

if($_SERVER['REQUEST_TIME'] - $trainer['hpnschk'] >= $system['happiness_check_cycle']) {

    DB::query('UPDATE pkm_trainerdata SET hpnschk = ' . $_SERVER['REQUEST_TIME'] . ' WHERE uid = ' . $trainer['uid']);
    DB::query('UPDATE pkm_mypkm SET hpns = hpns + ' . rand(1, 2) . ' WHERE uid = ' . $trainer['uid'] . ' AND place IN (1, 2, 3, 4, 5, 6)');

}


if(INAJAX && !empty($index)) {

    if(empty($trainer['uid'])) exit;
    if(empty($trainer['sttchk'])) $index = 'starter';
    if($index === 'my') $index = 'memcp';
    if($index === 'pc') $index = 'pkmcenter';

    $return = [];

    require_once(ROOT . '/source/ajax/' . $index . '.php');

    if($trainer['inbtl'] === '1' && !in_array($index, ['battle', 'map']))

        DB::query('UPDATE pkm_trainerdata SET inbtl = 0 WHERE uid = ' . $trainer['uid']);

    echo Kit::JsonConvert($return);

    goto END;

} else {

    empty($_GET['section']) && $_GET['section'] = '';

    ($index === 'my') && $index = 'memcp';
    ($index === 'pc') && $index = 'pkmcenter';
    empty($trainer['sttchk']) && $index = 'starter';
    empty($trainer['uid']) && $index = 'index';

    include ROOT . '/source/index/' . $index . '.php';

    if(!INAJAX) include template('index/' . $index, 'pkm');

}

END: {
    Trainer::SaveTemporaryStat();
}