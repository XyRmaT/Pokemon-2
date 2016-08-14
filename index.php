<?php
define('INPOKE', TRUE);
define('INAJAX', isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');
define('ROOT', __DIR__);
define('YEAR', date('Y', $_SERVER['REQUEST_TIME']));
define('TEMPLATEID', 1);
define('DEBUG_MODE', FALSE);
define('LANGUAGE', isset($_GET['lang']) && in_array($_GET['lang'], ['zh', 'en', 'de']) ? $_GET['lang'] : 'zh');

const ROOT_IMAGE    = './source-img';
const ROOT_TEMPLATE = './source-tpl';
const ROOT_CACHE    = './cache';
const ROOT_RELATIVE = '.';
const ROOT_DATA     = './data';

include_once ROOT . '/include/class/common.php';
date_default_timezone_set('Asia/Shanghai');
Kit::Library('class', ['trainer', 'obtain', 'pokemon', 'encounter']);
App::Initialize();
//App::register('pokeuniv@gmail.com', 'dwpyk5rz', 'dooooooduo');
//print_r(App::loginByEmail('pokeuniv@gmail.com', 'dwpyk5rz'));

// Loading Smarty template engine and setting up CSS parser
include_once ROOT . '/include/smarty/Smarty.class.php';
$smarty = new Smarty();
$smarty
    ->setCacheDir(ROOT . '/cache/template/')
    ->setTemplateDir(ROOT . '/source-tpl/index/')
    ->setCompileDir(ROOT . '/source-tpl/_compile/')
    ->setConfigDir(ROOT . '/include/smarty/config/')
    ->setDebugging(FALSE);


Cache::setCachePath(ROOT_CACHE);
Cache::setCSSPath(ROOT_TEMPLATE . '/stylesheet');

error_reporting(E_ALL);

// If the system is closed and it is not GM visiting, display the error message
if($system['system_switch'] === 0) {
    exit($system['close_reason']);
}

// Set up some global variables, also keep the minified CSS file up to date
$allow_pages = ['index', 'memcp', 'pc', 'copyright', 'starter', 'shop', 'daycare', 'battle', 'map', 'shelter'];
$index       = !empty($user['user_id']) && isset($_GET['index']) && in_array($_GET['index'], $allow_pages) ? $_GET['index'] : 'index';
$process     = $_GET['process'] ?? '';
$trainer     = [];
$r           = ['_LANG' => $lang];


if(!empty($user['user_id'])) {
    $trainer = Trainer::Fetch($user['user_id']);

    if(empty($trainer['has_starter'])) {
        $index = 'starter';
    } elseif($index === 'pc') {
        $index = 'pc';
    }

    // Each checking cycle randomly add 1~2 happiness to the party, and update the timer
    if($_SERVER['REQUEST_TIME'] - $trainer['time_happiness_checked'] >= $system['happiness_check_cycle']) {
        $add_happiness = rand($system['happiness_add']['min'], $system['happiness_add']['max']);
        DB::query('UPDATE pkm_trainerdata SET time_happiness_checked = ' . $_SERVER['REQUEST_TIME'] . ' WHERE user_id = ' . $trainer['user_id']);
        DB::query('UPDATE pkm_mypkm SET happiness = happiness + ' . $add_happiness . ' WHERE user_id = ' . $trainer['user_id'] . ' AND location IN (' . LOCATION_PARTY . ') AND happiness <= ' . (255 - $add_happiness));
    }

    // Updating last visit timestamp
    setcookie('last_visit', $_SERVER['REQUEST_TIME']);
    if(empty($_COOKIE['last_visit']) || $_COOKIE['last_visit'] + 300 < $_SERVER['REQUEST_TIME'] || !$trainer['time_last_visit'] || $trainer['time_last_visit'] + 300 < $_SERVER['REQUEST_TIME']) {
        DB::query('UPDATE pkm_trainerdata SET time_last_visit = ' . $_SERVER['REQUEST_TIME'] . ' WHERE user_id = ' . $trainer['user_id']);
    }
} else {
    $index = $index === 'starter' ? 'starter' : 'index';
}


if(INAJAX && !empty($index) && !empty($process)) {

    $return = [];

    include ROOT . '/source/ajax/' . $index . '.php';

    if($trainer['is_battling'] === '1' && !in_array($index, ['battle', 'map']))
        DB::query('UPDATE pkm_trainerdata SET is_battling = 0 WHERE user_id = ' . $trainer['user_id']);

    $return['data']['trainer'] = $trainer;
    $return['data']['system']  = $system;

    echo Kit::JsonConvert($return);

} else {

    empty($_GET['section']) && $_GET['section'] = '';

    include ROOT . '/source/index/' . $index . '.php';

    $r['trainer'] = $trainer;
    $r['system']  = $system;

    if(!INAJAX) {
        $path['css'] = Cache::css(['common', 'angular-sortable', $index], 'cssvar');
        $smarty->assign('r', $r);
        $smarty->assign('path', $path);
        $smarty->assign('user', $user);
        $smarty->assign('index', $index);
        $smarty->assign('system', $system);
        $smarty->assign('start_time', $start_time);
        $smarty->assign('lang', $lang);
        $smarty->display($index . '.tpl');
    } else {
        echo Kit::JsonConvert(['data' => $r]);
    }

}

END: {
    if(!empty($trainer['user_id'])) {
        Trainer::SaveTemporaryStat($trainer['user_id'], $trainer['stat_add']);
    }
}