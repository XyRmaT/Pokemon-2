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
define('DEBUG_MODE', FALSE);
define('LANGUAGE', isset($_GET['lang']) && in_array($_GET['lang'], ['zh', 'en', 'de']) ? $_GET['lang'] : 'zh');

include_once ROOT . '/include/class-common.php';
App::Initialize();

// Loading Smarty template engine and setting up CSS parser
include_once ROOT . '/include/smarty/Smarty.class.php';
$smarty               = new Smarty();
$smarty->template_dir = ROOT . '/source-tpl/index/';
$smarty->compile_dir  = ROOT . '/source-tpl/_compile/';
$smarty->config_dir   = ROOT . '/include/smarty/config/';
$smarty->cache_dir    = ROOT . '/cache/template/';
$smarty->debugging    = 0;
// $smarty->loadFilter('output', 'indent_html');

Cache::$path_cache = ROOT_CACHE;
Cache::$path_css   = ROOT_TEMPLATE . '/stylesheet';

error_reporting(E_ALL);

// If the system is closed and it is not GM visiting, display the error message
if($system['system_switch'] === 0 && $user['uid'] != 8)
    exit($system['close_reason']);

// Load up the essential libraries
Kit::Library('class', ['trainer', 'obtain', 'pokemon', 'encounter']);

// Set up some global variables, also keep the minified CSS file up to date
//$system            = array_merge($system, DB::fetch_first('SELECT shopsell, shopopc FROM pkm_stat'));
$index       = !empty($user['uid']) && isset($_GET['index']) && in_array($_GET['index'], ['memcp', 'pc', 'copyright', 'starter', 'shop', 'daycare', 'index', 'battle', 'map', 'shelter']) ? $_GET['index'] : 'index';
$process     = !empty($_GET['process']) ? $_GET['process'] : '';
$path['css'] = Cache::Css(['common', 'angular-sortable', $index], 'cssvar');
$trainer     = [];
$synclogin   = '';
$r           = ['_LANG' => $lang];
App::Login('嘟嘟之魂', 'wodaxiayiado');

// Change the default timezone to +8
date_default_timezone_set('Asia/Shanghai');

if(!empty($user['uid'])) {

    $trainer = Trainer::Fetch($user['uid']);

    // Generating trainer's info if not existed
    if(!$trainer) {
        Trainer::Generate($user['uid']);
        $trainer = Trainer::Fetch($user['uid']);
    }

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


    //DB::query('DELETE FROM pkm_mypkm');
    //DB::query('DELETE FROM pkm_mypokedex');
    //for($i = 0; $i < 666; $i++) {
    //    Pokemon::Generate(rand(1, 721), 8, ['is_shiny' => rand(0, 1), 'is_egg' =>rand(0, 1), 'met_location' => 9]);
    //}

    /*$query = DB::query('SELECT uid FROM pkm_trainerdata');
    while($info = DB::fetch($query)) {
        Obtain::TrainerCard(Trainer::Fetch($info['uid']), TRUE);
    }*/

}

$smarty->assign('user', $user);
$smarty->assign('index', $index);
$smarty->assign('system', $system);
$smarty->assign('synclogin', $synclogin);
$smarty->assign('path', $path);
$smarty->assign('start_time', $start_time);
$smarty->assign('lang', $lang);

if(INAJAX && !empty($index) && !empty($process)) {

    if(empty($user['uid'])) {
        $index = 'index';
    } elseif(empty($trainer['has_starter'])) {
        $index = 'starter';
    } elseif($index === 'pc') {
        $index = 'pc';
    }

    $return = [];

    require_once(ROOT . '/source/ajax/' . $index . '.php');

    if($trainer['is_battling'] === '1' && !in_array($index, ['battle', 'map']))
        DB::query('UPDATE pkm_trainerdata SET is_battling = 0 WHERE uid = ' . $trainer['uid']);

    $return['data']['trainer'] = $trainer;
    $return['data']['system']  = $system;

    echo Kit::JsonConvert($return);

} else {

    empty($_GET['section']) && $_GET['section'] = '';

    if(empty($user['uid'])) {
        $index = 'index';
    } elseif(empty($trainer['has_starter'])) {
        $index = 'starter';
    } elseif($index === 'pc') {
        $index = 'pc';
    }

    include ROOT . '/source/index/' . $index . '.php';

    $r['trainer'] = $trainer;
    $r['system']  = $system;

    if(!INAJAX) {
        $smarty->assign('r', $r);
        $smarty->display($index . '.tpl');
    } else {
        echo Kit::JsonConvert(['data' => $r]);
    }

}

END: {
    if(!empty($trainer['uid'])) Trainer::SaveTemporaryStat($trainer['uid'], $trainer['stat_add']);
}