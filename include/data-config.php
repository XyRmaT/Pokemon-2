<?php

$system = [
    'pkm_per_box'           => 30,
    'initial_box'           => 6,
    'system_switch'         => TRUE,
    'close_reason'          => '系统更新中，请稍后再试！',
    'admins'                => '8', // ,1144,1333
    'version'               => '1.0.0',
    'per_item_limit'        => 100,
    'day_division'          => [
        'morning' => range(4, 9),
        'day'     => range(10, 19),
        'evening' => range(20, 3)
    ],
    'fmPlaceCost'           => 10,
    'currency_name'         => '弹珠',
    'currency_field'        => 'extcredits7',
    'c_1wk'                 => 0,
    'c_1mth'                => 0,
    'happiness_check_cycle' => 30 * 60,
    'happiness_add'         => ['min' => 10, 'max' => 20],
    'exp_field'             => 'extcredits1'
];

define('UC_CONNECT', 'mysql');
define('UC_DBHOST', '127.0.0.1');
define('UC_DBUSER', 'root');
define('UC_DBPW', '');
define('UC_DBNAME', 'pokeuniv');
define('UC_DBCHARSET', 'utf8');
define('UC_DBTABLEPRE', '`pokeuniv`.pre_ucenter_');
define('UC_DBCONNECT', '0');
define('UC_KEY', 'FUCKME');
define('UC_API', 'http://127.0.0.1/bbs/uc_server');
define('UC_CHARSET', 'utf-8');
define('UC_IP', '');
define('UC_APPID', '3');
define('UC_PPP', '20');