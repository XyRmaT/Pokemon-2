<?php

$SYS = [
    'perbox' => 30,
    'sttbox' => 6,
    'switch' => 1,
    'closereason' => '系统更新中，请稍后再试！',
    'admin' => '8',//,1144,1333',
    'version' => '1.0.0',
    'bagperlimit' => 100,
    'daySeparate' => array(
        'morning' => range(4, 9),
        'day' => range(10, 19),
        'evening' => range(20, 3)
    ),
    'fmPlaceCost' => 10,
    'moneyname' => '弹珠',
    'moneyext' => 'extcredits7',
    'c_1wk' => 0,
    'c_1mth' => 0,
    'hpnschktime' => 1800,
    'expext' => 'extcredits1'
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