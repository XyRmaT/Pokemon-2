<?php

const DB_FIELD_STRING = 1;
const DB_FIELD_NUMBER = 2;

const ERROR_NOT_EMAIL = 1;
const ERROR_INVALID_TRAINER_NAME = 2;
const ERROR_TRAINER_EXISTED = 3;
const ERROR_DUPLICATE_EMAIL = 4;
const ERROR_DUPLICATE_TRAINER_NAME = 5;

const TYPE_FIRE     = 1;
const TYPE_WATER    = 2;
const TYPE_GRASS    = 3;
const TYPE_ELECTRIC = 4;
const TYPE_NORMAL   = 5;
const TYPE_FIGHTING = 6;
const TYPE_FLYING   = 7;
const TYPE_BUG      = 8;
const TYPE_POISON   = 9;
const TYPE_ROCK     = 10;
const TYPE_GROUND   = 11;
const TYPE_STEEL    = 12;
const TYPE_ICE      = 13;
const TYPE_PSYCHIC  = 14;
const TYPE_DARK     = 15;
const TYPE_GHOST    = 16;
const TYPE_DRAGON   = 17;
const TYPE_FAIRY    = 18;

const EGGGROUP_FIELD        = 1;
const EGGGROUP_BUG          = 2;
const EGGGROUP_FLYING       = 3;
const EGGGROUP_MONSTER      = 4;
const EGGGROUP_FAIRY        = 5;
const EGGGROUP_HUMANLIKE    = 6;
const EGGGROUP_MINERAL      = 7;
const EGGGROUP_GRASS        = 8;
const EGGGROUP_WATER1       = 9;
const EGGGROUP_WATER2       = 10;
const EGGGROUP_WATER3       = 11;
const EGGGROUP_DRAGON       = 12;
const EGGGROUP_AMORPHOUS    = 13;
const EGGGROUP_DITTO        = 14;
const EGGGROUP_UNDISCOVERED = 15;

const STATUS_BURN      = 1;
const STATUS_FREEZE    = 2;
const STATUS_PARALYSIS = 3;
const STATUS_SLEEP     = 4;
const STATUS_POISON    = 5;
const STATUS_TOXIC     = 6;

const GENDERLESS    = 0;
const GENDER_MALE   = 1;
const GENDER_FEMALE = 2;

const ITEM_TYPE_POKEBALL = 1;
const ITEM_TYPE_EVOSTONE = 2;
const ITEM_TYPE_HOLD     = 3;
const ITEM_TYPE_MEDICINE = 4;

const LOCATION_PARTY   = '1, 2, 3, 4, 5, 6';
const LOCATION_DAYCARE = 7;
const LOCATION_PCHEAL  = 8;
const LOCATION_SHELTER = 9;
const LOCATION_TRADE   = 10;
define('LOCATION_BOX', implode(',', range(101, 200)));

const MOVE_BY_LEVEL = 1;
const MOVE_BY_TMHM  = 2;


const MOVECLASS_STATUS   = 0;
const MOVECLASS_PHYSICAL = 1;
const MOVECLASS_SPECIAL  = 2;

define('FIELDS_POKEMON_BASIC',
    'm.pkm_id, m.nat_id, m.gender, m.sprite_name, m.nickname, m.level, m.item_holding, ' .
    'm.item_captured, m.status, m.happiness, m.form, m.exp, m.location');
define('FIELDS_POKEMON_LEVELUP', FIELDS_POKEMON_BASIC .
    ',p.exp_type, p.evolution_data, p.base_stat, p.name_zh name, m.level, m.exp, m.pkm_id, m.nature, ' .
    'm.nat_id, m.moves, m.new_moves, m.idv_value, m.eft_value, m.hp');
define('FIELDS_POKEMON_DETAILED', 'm.nat_id, m.pkm_id, m.gender, m.hp, m.exp, m.level, m.nature, ' .
    'm.nickname, m.form, m.eft_value, m.idv_value, m.new_moves, m.moves, ' .
    'm.sprite_name, m.item_captured, m.time_hatched, m.met_time, m.met_level, ' .
    'm.met_location, m.beauty, m.item_holding, m.happiness, m.psn_value, ' .
    'm.form, m.initial_user_id, m.status, ' .
    'a.name_zh ability, p.base_stat, p.type, p.type_b, p.exp_type, p.name_zh name, p.evolution_data,' .
    'mb.trainer_name');