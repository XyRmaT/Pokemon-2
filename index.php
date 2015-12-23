<?php

define('INPOKE', TRUE);
define('INAJAX', (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest' || !empty($_GET['aaa']) && $_GET['aaa'] === '1') ? TRUE : FALSE);
define('ROOT', dirname(__FILE__));
define('YEAR', date('Y', $_SERVER['REQUEST_TIME']));
define('TEMPLATEID', 1);
define('ROOT_IMAGE', './source-img');
define('ROOT_TEMPLATE', './source-tpl');
define('ROOT_CACHE', './cache');
define('ROOT_RELATIVE', '.');

include_once ROOT . '/include/class-common.php';
App::Initialize();

// Loading Smarty template engine and setting up CSS parser
include_once ROOT . '/include/smarty/Smarty.class.php';
$smarty               = new Smarty();
$smarty->template_dir = ROOT . '/source-tpl/index/';
$smarty->compile_dir  = ROOT . '/source-tpl/_compile/';
$smarty->config_dir   = ROOT . '/include/smarty/config/';
$smarty->cache_dir    = ROOT . '/cache/template/';
$smarty->debugging    = TRUE;

Cache::$path_cache = ROOT_CACHE;
Cache::$path_css   = ROOT_TEMPLATE . '/stylesheet';

error_reporting(E_ALL);

// If the system is closed and it is not GM visiting, display the error message
if($system['system_switch'] === 0 && $user['uid'] != 8)
    exit($system['close_reason']);

// Load up the essential libraries
Kit::Library('class', ['trainer', 'obtain']);


// Set up some global variables, also keep the minified CSS file up to date
//$system            = array_merge($system, DB::fetch_first('SELECT shopsell, shopopc FROM pkm_stat'));
$index       = !empty($user['uid']) && isset($_GET['index']) && in_array($_GET['index'], ['my', 'pc', 'copyright', 'starter', 'shop', 'daycare', 'index', 'battle', 'map', 'tempview', 'tempaward', 'ranking', 'shelter']) ? $_GET['index'] : 'index';
$path['css'] = Cache::Css(['common', $index], 'cssvar');
$trainer     = [];
$synclogin   = '';
//App::Login('嘟嘟之魂', 'wodaxiayiado');

// Change the default timezone to +8
date_default_timezone_set('Asia/Shanghai');

if(!empty($user['uid'])) {

    $trainer = Trainer::Fetch($user['uid']);

    // Generating trainer's info if not existed
    if(!$trainer) {
        Trainer::Generate($trainer['uid']);
        $trainer = Trainer::Fetch($user['uid']);
    }

    $trainer['username']  = $user['username'];
    $trainer['extcredit'] = DB::fetch_first('SELECT ' . $system['currency_field'] . ' currency, ' . $system['exp_field'] . ' exp FROM pre_common_member_count WHERE uid = ' . $trainer['uid']);
    $trainer['gm']        = in_array($trainer['uid'], explode(',', $system['admins']));
    $trainer['currency']  = $trainer['extcredit']['currency'];
    $trainer['avatar']    = Obtain::Avatar($trainer['uid']);
    $trainer['stat_add']  = array_map(function () { return 0; }, $trainer['stat']);

    // Add EXP gained from forum posts to the party
    if($trainer['extcredit']['exp'] > 0 && $trainer['has_starter']) {
        DB::query('UPDATE pkm_mypkm SET exp = exp + ' . $trainer['extcredit']['exp'] . ' WHERE uid = ' . $trainer['uid'] . ' AND location IN (1, 2, 3, 4, 5, 6)');
        App::CreditsUpdate($trainer['uid'], 0, 'EXP', TRUE);
    }

    // Each checking cycle randomly add 1~2 happiness to the party, and update the timer
    if($_SERVER['REQUEST_TIME'] - $trainer['time_happiness_checked'] >= $system['happiness_check_cycle']) {
        $add_happiness = rand($system['happiness_add']['min'], $system['happiness_add']['max']);
        DB::query('UPDATE pkm_trainerdata SET time_happiness_checked = ' . $_SERVER['REQUEST_TIME'] . ' WHERE uid = ' . $trainer['uid']);
        DB::query('UPDATE pkm_mypkm SET happiness = happiness + ' . $add_happiness . ' WHERE uid = ' . $trainer['uid'] . ' AND location IN (1, 2, 3, 4, 5, 6) AND happiness <= ' . (255 - $add_happiness));
    }

    // Updating last visit timestamp
    setcookie('last_visit', $_SERVER['REQUEST_TIME']);
    if(empty($_COOKIE['last_visit']) || $_COOKIE['last_visit'] + 300 < $_SERVER['REQUEST_TIME'] ||
        !$trainer['time_last_visit'] || $trainer['time_last_visit'] + 300 < $_SERVER['REQUEST_TIME'])
        DB::query('UPDATE pkm_trainerdata SET time_last_visit = ' . $_SERVER['REQUEST_TIME'] . ' WHERE uid = ' . $trainer['uid']);

    unset($trainer['extcredit']);

}


$smarty->assign('trainer', $trainer);
$smarty->assign('user', $user);
$smarty->assign('index', $index);
$smarty->assign('system', $system);
$smarty->assign('synclogin', $synclogin);
$smarty->assign('lang', $lang);
$smarty->assign('path', $path);
$smarty->assign('start_time', $start_time);

if(INAJAX && !empty($index) && !empty($_GET['process'])) {

    if(empty($user['uid'])) $index = 'index';
    elseif(empty($trainer['has_starter'])) $index = 'starter';
    elseif($index === 'my') $index = 'memcp';
    elseif($index === 'pc') $index = 'pkmcenter';

    $return = [];

    require_once(ROOT . '/source/ajax/' . $index . '.php');

    if($trainer['is_battling'] === '1' && !in_array($index, ['battle', 'map']))
        DB::query('UPDATE pkm_trainerdata SET is_battling = 0 WHERE uid = ' . $trainer['uid']);

    echo Kit::JsonConvert($return);

    goto END;

} else {

    empty($_GET['section']) && $_GET['section'] = '';

    if(empty($user['uid'])) $index = 'index';
    elseif(empty($trainer['has_starter'])) $index = 'starter';
    elseif($index === 'my') $index = 'memcp';
    elseif($index === 'pc') $index = 'pkmcenter';

    include ROOT . '/source/index/' . $index . '.php';

    $smarty->display($index . '.tpl');

}

END: {
    if(!empty($trainer['uid'])) Trainer::SaveTemporaryStat($trainer['uid'], $trainer['stat_add']);
}