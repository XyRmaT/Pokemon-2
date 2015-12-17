<?php

switch($_GET['process']) {
    case 'update':

        $_GET['mapid'] = 1;
        $query         = DB::query('SELECT uid, username, coord_x, coord_y, map_id FROM pkm_mapcoordinate WHERE map_id = ' . intval($_GET['mapid']) . ' AND uid != ' . $trainer['uid']);
        $data          = [];

        while($info = DB::fetch($query))
            $data[] = '[' . $info['uid'] . ', \'' . $info['username'] . '\', ' . $info['x'] . ', ' . $info['y'] . ']';

        $return['js'] = 'p([' . implode(',', $data) . ']);';

        break;
    case 'walk':

        $_GET['mapid'] = 1;

        $mapid = DB::result_first('SELECT map_id FROM pkm_mapcoordinate WHERE uid = ' . $trainer['uid']);
        $mapid = !$mapid ? intval($_GET['mapid']) : $mapid;

        if(!$mapid || $mapid && $mapid != $_GET['mapid'] || !file_exists($filepath = ROOT . '/data/map/map-' . $mapid . '.php')) exit;

        include $filepath;

        $x = intval($_GET['x']);
        $y = intval($_GET['y']);

        if($_tiles[$x]{$y} === '1') exit;

        DB::query('INSERT INTO pkm_mapcoordinate (uid, username, x, y, map_id, time)
                   VALUES (' . $trainer['uid'] . ', \'' . $trainer['username'] . '\', ' . $x . ', ' . $y . ', ' . $mapid . ', ' . $_SERVER['REQUEST_TIME'] . ')
                   ON DUPLICATE KEY UPDATE x = VALUES(x), y = VALUES(y), time = VALUES(time)');

        $return['console'] = '';

        if(DB::result_first('SELECT COUNT(*) FROM pkm_mypkm WHERE location IN (1, 2, 3, 4, 5, 6) AND hp > 0 AND id != 0 AND uid = ' . $trainer['uid']) > 0) {

            Kit::Library('class', ['battle', 'pokemon']);


            //  Obtaining self pokemon's information

            $query = DB::query('SELECT m.pkm_id, m.nat_id, m.ability, m.exp, m.nickname name_zh name, m.gender, m.psn_value, m.ind_value, m.eft_value, m.nature, m.level, m.item_carrying, m.happiness, m.moves, m.ability, m.hp, m.status, m.uid, m.sprite_name, m.moves_new, m.happiness, m.beauty, m.form, p.name nickname, m.uid_initial, p.base_stat, p.type, p.type_b, p.evolution_data, p.exp_type, p.baseexp, p.ability_dream, p.height, p.weight FROM pkm_mypkm m LEFT JOIN pkm_pkmdata p ON m.nat_id = p.nat_id WHERE m.uid = ' . $trainer['uid'] . ' AND m.location IN (1, 2, 3, 4, 5, 6) AND m.nat_id != 0 ORDER BY m.location ASC');

            $i = 1;

            while($info = DB::fetch($query)) {

                if($i === 1 && $info['hp'] < 1)

                    $return['js'] = 'DISABLE.BATTLEEND = true;';

                $info['height']         = $info['height'] / 10;
                $info['weight']         = $info['weight'] / 10;
                $info['gendersign']     = Obtain::GenderSign($info['gender']);
                $info['moves']           = unserialize($info['moves']);
                $info['moves_new']        = !empty($info['moves_new']) ? unserialize($info['moves_new']) : [];
                Battle::$pokemon[$i]    = [$info, Battle::GenerateBattleData($info['pkm_id'])];
                Battle::$pokemon[$i][0] = array_merge(Battle::$pokemon[$i][0],
                    Obtain::Stat(
                        Battle::$pokemon[$i][0]['level'],
                        Battle::$pokemon[$i][0]['base_stat'],
                        Battle::$pokemon[$i][0]['ind_value'],
                        Battle::$pokemon[$i][0]['eft_value'],
                        Battle::$pokemon[$i][0]['nature'],
                        Battle::$pokemon[$i][0]['hp']
                    )
                );

                ++$i;

            }

            Battle::$pokemon[1][1][4] = 1; // participated


            $tmp   = [];
            $query = DB::query('SELECT id, levelmin, levelmax, rate FROM pkm_encounterdata WHERE map_id = ' . $mapid . ' AND timefrom < ' . $_SERVER['REQUEST_TIME'] . ' AND (timeto = 0 OR timeto > ' . $_SERVER['REQUEST_TIME'] . ') AND qty != 0');

            while($info = DB::fetch($query))

                $tmp[] = $info;

            if(empty($tmp)) break;

            $i = 0;

            for(; ;) {

                $appearpkm = $tmp[array_rand($tmp)];

                if(rand(1, 100) <= $appearpkm['rate']) {

                    $appearpkm['level'] = rand($appearpkm['levelmin'], $appearpkm['levelmax']);

                    $appearpkm['level'] = 50;

                    break;

                }

                if(++$i > 100) break;

            }

            if($i > 50) break;

            DB::query('INSERT INTO pkm_battlefield (uid) VALUES (' . $trainer['uid'] . ') ON DUPLICATE KEY UPDATE weather = 0, has_trickroom = 0, has_gravity = 0');
            DB::query('UPDATE pkm_trainerdata SET is_battling = 1 WHERE uid = ' . $trainer['uid']);

            Battle::$pokemon[0] = [
                Pokemon::Generate($appearpkm['nat_id'], $trainer['uid'], [
                    'met_location' => $mapid,
                    'met_level' => $appearpkm['level'],
                    'wild'    => 1
                ]),
                Battle::GenerateBattleData()
            ];

            Battle::$pokemon[0][0]               = array_merge(
                Battle::$pokemon[0][0], Obtain::Stat(
                Battle::$pokemon[0][0]['level'],
                Battle::$pokemon[0][0]['base_stat'],
                Battle::$pokemon[0][0]['ind_value'],
                Battle::$pokemon[0][0]['eft_value'],
                Battle::$pokemon[0][0]['nature'],
                Battle::$pokemon[0][0]['hp']
            )
            );
            Battle::$pokemon[0][0]['gendersign'] = Obtain::GenderSign(Battle::$pokemon[0][0]['gender']);
            Battle::$pokemon[0][0]['moves']       = unserialize(Battle::$pokemon[0][0]['moves']);

            ksort(Battle::$pokemon);

            Battle::$pokemon[7] = Battle::$pokemon[8] = Battle::GenerateFieldData();

            Battle::ReorderPokemon();

            Battle::WriteBattleData($trainer['uid'], Battle::$pokemon);

            Battle::$report = '野生的' . Battle::$pokemon[0][0]['name'] . '出现了！<br>' . $trainer['username'] . '派出了' . Battle::$pokemon[1][0]['name'] . '！<br>';

            if(Pokemon::Register($appearpkm['nat_id']) === FALSE)

                Battle::$report .= Battle::$pokemon[0][0]['name'] . '被登记在了图鉴中。<br>';

            Battle::$report .= '<br>';


            /*
                Initiate battle layer
                display pokemon info
            */

            $return['js'] = 'initbattle();$(\'#sbj-oppo\').html(\'' . Battle::$pokemon[0][0]['name'] . Battle::$pokemon[0][0]['gendersign'] . ' Lv. ' . Battle::$pokemon[0][0]['level'] . ' ' . Obtain::StatusIcon(Battle::$pokemon[0][0]['status']) . '<div class="bar"><div class="hp" style="width:' . Battle::$pokemon[0][0]['hpper'] . '%"></div><div class="value">' . Battle::$pokemon[0][0]['hp'] . '/' . Battle::$pokemon[0][0]['maxhp'] . '</div></div><div class="sprite"><img src="' . Obtain::Sprite('pokemon', 'png', Battle::$pokemon[0][0]['sprite_name']) . '"></div>\').show().animate({left:\'750px\'}, 3000);
            $(\'#sbj-self\').show().html(\'' . Battle::$pokemon[1][0]['name'] . Battle::$pokemon[1][0]['gendersign'] . ' Lv. ' . Battle::$pokemon[1][0]['level'] . ' ' . Obtain::StatusIcon(Battle::$pokemon[1][0]['status']) . '<div class="bar"><div class="hp" style="width:' . Battle::$pokemon[1][0]['hpper'] . '%"></div><div class="value">' . Battle::$pokemon[1][0]['hp'] . '/' . Battle::$pokemon[1][0]['maxhp'] . '</div></div><div class="sprite"><img src="' . Obtain::Sprite('pokemon', 'png', Battle::$pokemon[1][0]['sprite_name'], 0, 1) . '"></div>\').show().animate({left:\'105px\'}, 2000);$(\'#btl-report\').show().html(\'' . Battle::$report . '\');';


            /*
                Generate pokemon moves info
            */

            $tmp = '';

            foreach(Battle::$pokemon[1][0]['moves'] as $val) {

                $tmp .= '<div data-move_id="' . $val[0] . '"' . (($val[1] <= 0) ? ' class="disabled"' : '') . '>' . $val[2] . ' <em>' . $val[1] . '/' . ($val[3] + $val[3] * $val[4] / 5) . '</em></div>';

            }

            $return['js'] .= '$(\'#obj-moves\').html(\'' . $tmp . '\');';


            /*
                Generate item info
            */

            $tmp = '';

            foreach(Obtain::BagItem('(i.type = 1 AND i.is_usable = 0 OR i.type = 4 AND i.battle_effect != \'\' OR i.effect != \'\')', 'i.type ASC', 'GROUPED:type') as $val) {

                $tmp .= '<strong>' . Obtain::ItemClassName($val[0]['type']) . '</strong><ul>';

                foreach($val as $valb) {

                    $tmp .= '<li data-item_id="' . $valb['item_id'] . '" title="' . $valb['name'] . '（余' . $valb['quantity'] . '个）：' . $valb['description'] . '"><img src="' . Obtain::Sprite('item', 'png', 'item_' . $valb['item_id']) . '"></li>';

                }

                $tmp .= '</ul><br clear="both">';

            }

            $return['js'] .= '$(\'#layer-item\').html(\'' . (!empty($tmp) ? $tmp : '你的背包空空如也！') . '\');';

            /*
                Generate pokemon info
            */

            $tmp = '';

            foreach(Battle::$pokemon as $key => $val) {

                if($key < 2 || $key > 6 || $val[0]['hp'] < 1) continue;

                $tmp .= '<li data-pkm_id="' . $val[0]['pkm_id'] . '"><img src="' . ROOT_IMAGE . '/pokemon-icon/' . $val[0]['nat_id'] . '.png"> ' . $val[0]['name'] . ' Lv.' . $val[0]['level'] . '</li>';

            }

            $return['js'] .= '$(\'#layer-pokemon\').html(\'' . (!empty($tmp) ? '<ul>' . $tmp . '</ul>' : '没有可战斗的精灵。') . '\');';


            define('MODE', FALSE);

            if(MODE === 1) {

                $sql   = [];
                $query = DB::query('SELECT pkm_id, id, level FROM pkm_mypkm');
                while($info = DB::fetch($query)) {
                    $move   = [];
                    $queryb = DB::query('SELECT pm.move_id, md.pp, md.name FROM pkm_pkmmove pm LEFT JOIN pkm_movedata md ON md.move_id = pm.move_id WHERE pm.way = 1 AND pm.id = ' . $info['nat_id'] . ' AND pm.level <= ' . $info['level'] . ' ORDER BY pm.level DESC LIMIT 4');
                    while($infob = DB::fetch($queryb)) {
                        $move[] = [$infob['move_id'], $infob['pp'], $infob['name'], $infob['pp'], 0];
                    }
                    $sql[] = '(' . $info['pkm_id'] . ', \'' . serialize($move) . '\')';
                }
                DB::query('INSERT INTO pkm_mypkm (pkm_id, moves) VALUES ' . implode(',', $sql) . ' ON DUPLICATE KEY UPDATE moves = VALUES(moves)');
            } elseif(MODE === 2) {
                $query = DB::query('SELECT move_id, pp, name FROM pkm_movedata');
                $move  = [];
                while($info = DB::fetch($query)) {
                    $move[$info['pkm_id']] = $info;
                }
                $query = DB::query('SELECT pkm_id, moves FROM pkm_mypkm');
                $sql   = [];
                while($info = DB::fetch($query)) {
                    $info['moves'] = unserialize($info['moves']);
                    foreach($info['moves'] as $key => $val) {
                        $info['moves'][$key] = [$val[0], $move[$val[0]]['pp'], $move[$val[0]]['name'], $move[$val[0]]['pp'], 0];
                    }
                    $sql[] = '(' . $info['pkm_id'] . ', \'' . serialize($info['moves']) . '\')';
                }
                DB::query('INSERT INTO pkm_mypkm (pkm_id, moves) VALUES ' . implode(',', $sql) . ' ON DUPLICATE KEY UPDATE moves = VALUES(moves)');
            }


        } else {

            $return['console'] .= 'No pokemon in party have ability to battle! ';

        }

        $return['console'] .= 'Walk to (' . $x . ', ' . $y . ')';

        break;
}

END: