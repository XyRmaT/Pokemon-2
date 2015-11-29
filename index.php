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


// Include common libraries and initialize app

include_once ROOT . '/include/data-config.php';
include_once ROOT . '/../bbs/uc_client/client.php';
error_reporting(E_ALL);
include_once ROOT . '/include/function-global.php';
include_once ROOT . '/include/class-common.php';
include_once ROOT . '/include/class-database.php';
include_once ROOT . '/include/class-cache.php';


// If the system is closed and it is not GM visiting, display the error message

if($SYS['switch'] === 0 && $_G['uid'] != 8) exit($SYS['closereason']);


// Load up the essential libraries

Kit::Library('class', ['trainer', 'obtain']);


// Set up some global variables, also keep the minified CSS file up to date

//$SYS            = array_merge($SYS, DB::fetch_first('SELECT shopsell, shopopc FROM pkm_stat'));
$index       = isset($_GET['index']) && in_array($_GET['index'], ['my', 'pc', 'copyright', 'starter', 'shop', 'daycare', 'index', 'battle', 'map', 'tempview', 'tempaward', 'ranking', 'shelter']) ? $_GET['index'] : 'index';
$path['css'] = Cache::Css(['index/stylesheet', 'css/jquery-ui-1.10.3.custom'], 'index/cssvar');


// Change the default timezone to +8

date_default_timezone_set('Asia/Shanghai');

if(!empty($_G['uid'])) {

    $user = DB::fetch_first('SELECT t.uid, t.trainer_id, t.exp, t.level, t.has_starter, t.box_quantity, t.time_happiness_checked, t.is_battling, t.has_new_message,
                              ts.uid exist, ts.pkm_evolved, ts.item_bought, ts.pkm_traded, ts.pkm_hatched
                              FROM pkm_trainerdata t
                              LEFT JOIN pkm_trainerstat ts
                              ON ts.uid = t.uid
                              WHERE t.uid = ' . $_G['uid']);

    if(!$user) {

        Trainer::Generate($_G['uid']);

    } else {

        $user['extcredit'] = DB::fetch_first('SELECT ' . $SYS['moneyext'] . ', ' . $SYS['expext'] . ' FROM pre_common_member_count WHERE uid = ' . $_G['uid']);
        $user['gm']        = in_array($_G['uid'], explode(',', $SYS['admin']));
        $user['money']     = $user['extcredit'][$SYS['moneyext']];
        $user['avatar']    = Obtain::Avatar($_G['uid']);

        // Add EXP gained from forum posts to the party, and clear the counter

        if(!empty($user['sttchk']) && $user['extcredit'][$SYS['expext']] > 0) {

            DB::query('UPDATE pkm_mypkm SET exp = exp + ' . $user['extcredit'][$SYS['expext']] . ' WHERE uid = ' . $_G['uid'] . ' AND place IN (1, 2, 3, 4, 5, 6)');
            DB::query('UPDATE pre_common_member_count SET ' . $SYS['expext'] . ' = 0 WHERE uid = ' . $_G['uid']);

            $user['extcredit'][$SYS['expext']] = 0;

        }

    }

    // Set up the old & new stat counter

    if(!$user['exist']) {

        DB::query('INSERT INTO pkm_trainerstat (uid) VALUES (' . $user['uid'] . ')');

        $user['stat']['old'] = $user['stat']['new'] = [
            'pmevolve' => 0,
            'itembuy'  => 0,
            'pmhatch'  => 0
        ];

    } else {

        $user['stat']['old'] = $user['stat']['new'] = [
            'pmevolve' => $user['pmevolve'],
            'itembuy'  => $user['itembuy'],
            'pmhatch'  => $user['pmhatch']
        ];

    }

}


// Each half an hour randomly add 1~2 happiness to the party, and update the timer

if($_SERVER['REQUEST_TIME'] - $user['hpnschk'] >= $SYS['hpnschktime']) {

    DB::query('UPDATE pkm_trainerdata SET hpnschk = ' . $_SERVER['REQUEST_TIME'] . ' WHERE uid = ' . $_G['uid']);
    DB::query('UPDATE pkm_mypkm SET hpns = hpns + ' . rand(1, 2) . ' WHERE uid = ' . $_G['uid'] . ' AND place IN (1, 2, 3, 4, 5, 6)');

}


if(INAJAX && !empty($index)) {

    if(empty($_G['uid'])) exit;
    if(empty($user['sttchk'])) $index = 'starter';
    if($index === 'my') $index = 'memcp';
    if($index === 'pc') $index = 'pkmcenter';

    $return = [];

    require_once(ROOT . '/source/ajax/' . $index . '.php');

    if($user['inbtl'] === '1' && !in_array($index, ['battle', 'map']))

        DB::query('UPDATE pkm_trainerdata SET inbtl = 0 WHERE uid = ' . $user['uid']);

    echo Kit::JsonConvert($return);

    goto END;

} else {

    empty($_GET['section']) && $_GET['section'] = '';

    ($index === 'my') && $index = 'memcp';
    ($index === 'pc') && $index = 'pkmcenter';
    empty($user['sttchk']) && $index = 'starter';
    empty($_G['uid']) && $index = 'index';

    include ROOT . '/source/index/' . $index . '.php';

    if(!INAJAX) include template('index/' . $index, 'pkm');

}

END: {
    Trainer::SaveTemporaryStat();
}