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

define('ITEM_TYPE_POKEBALL', 1);
define('ITEM_TYPE_EVOSTONE', 2);
define('ITEM_TYPE_HOLD', 3);
define('ITEM_TYPE_MEDICINE', 4);

define('LOCATION_PARTY', '1, 2, 3, 4, 5, 6');
define('LOCATION_DAYCARE', 7);
define('LOCATION_PCHEAL', 8);
define('LOCATION_SHELTER', 9);
define('LOCATION_TRADE', 10);
define('LOCATION_BOX', implode(',', range(101, 200)));

define('MOVE_BY_LEVEL', 1);
define('MOVE_BY_TMHM', 2);

define('FIELDS_POKEMON_BASIC',
    'm.pkm_id, m.nat_id, m.gender, m.sprite_name, m.nickname, m.level, m.item_holding, ' .
    'm.item_captured, m.status, m.happiness, m.form, m.exp, m.location');
define('FIELDS_POKEMON_LEVELUP', FIELDS_POKEMON_BASIC .
    ',p.exp_type, p.evolution_data, p.base_stat, p.name_zh name, m.level, m.exp, m.pkm_id, m.nature, ' .
    'm.nat_id, m.moves, m.new_moves, m.ind_value, m.eft_value, m.hp');
define('FIELDS_POKEMON_DETAILED', 'm.nat_id, m.pkm_id, m.gender, m.hp, m.exp, m.level, m.nature, ' .
    'm.nickname, m.form, m.eft_value, m.ind_value, m.new_moves, m.moves, ' .
    'm.sprite_name, m.item_captured, m.time_hatched, m.met_time, m.met_level, ' .
    'm.met_location, m.beauty, m.item_holding, m.happiness, m.psn_value, ' .
    'm.form, m.uid_initial, m.status, ' .
    'a.name_zh ability, p.base_stat, p.type, p.type_b, p.exp_type, p.name_zh name, p.evolution_data,' .
    'mb.username');