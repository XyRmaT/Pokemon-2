<?php

define('TYPE_FIRE', 1);
define('TYPE_WATER', 2);
define('TYPE_GRASS', 3);
define('TYPE_ELECTRIC', 4);
define('TYPE_NORMAL', 5);
define('TYPE_FIGHTING', 6);
define('TYPE_FLYING', 7);
define('TYPE_BUG', 8);
define('TYPE_POISON', 9);
define('TYPE_ROCK', 10);
define('TYPE_GROUND', 11);
define('TYPE_STEEL', 12);
define('TYPE_ICE', 13);
define('TYPE_PSYCHIC', 14);
define('TYPE_DARK', 15);
define('TYPE_GHOST', 16);
define('TYPE_DRAGON', 17);
define('TYPE_FAIRY', 18);

define('EGGGROUP_FIELD', 1);
define('EGGGROUP_BUG', 2);
define('EGGGROUP_FLYING', 3);
define('EGGGROUP_MONSTER', 4);
define('EGGGROUP_FAIRY', 5);
define('EGGGROUP_HUMANLIKE', 6);
define('EGGGROUP_MINERAL', 7);
define('EGGGROUP_GRASS', 8);
define('EGGGROUP_WATER1', 9);
define('EGGGROUP_WATER2', 10);
define('EGGGROUP_WATER3', 11);
define('EGGGROUP_DRAGON', 12);
define('EGGGROUP_AMORPHOUS', 13);
define('EGGGROUP_DITTO', 14);
define('EGGGROUP_UNDISCOVERED', 15);

define('LOCATION_PARTY', '1, 2, 3, 4, 5, 6');
define('LOCATION_DAYCARE', 7);
define('LOCATION_PCHEAL', 8);
define('LOCATION_SHELTER', 9);
define('LOCATION_TRADE', 10);
define('LOCATION_BOX', implode(',', range(101, 200)));

define('MOVE_BY_LEVEL', 1);
define('MOVE_BY_TMHM', 2);