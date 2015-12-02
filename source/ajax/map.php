<?php

switch($_GET['process']) {
    case 'update':

        $_GET['mapid'] = 1;
        $query         = DB::query('SELECT uid, username, x, y, map_id FROM pkm_mapcoordinate WHERE map_id = ' . intval($_GET['mapid']) . ' AND uid != ' . $trainer['uid']);
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

        if(DB::result_first('SELECT COUNT(*) FROM pkm_mypkm WHERE place IN (1, 2, 3, 4, 5, 6) AND hp > 0 AND id != 0 AND uid = ' . $trainer['uid']) > 0) {

            Kit::Library('class', ['battle', 'pokemon']);


            //  Obtaining self pokemon's information

            $query = DB::query('SELECT m.pid, m.id, m.abi, m.exp, m.nickname name_zh, m.gender, m.pv, m.iv, m.ev, m.nature, m.level, m.crritem, m.hpns, m.move, m.abi, m.hp, m.status, m.uid, m.imgname, m.newmove, m.hpns, m.beauty, m.form, p.name nickname, m.originuid, p.bs, p.type, p.typeb, p.evldata, p.exptype, p.baseexp, p.abic, p.height, p.weight FROM pkm_mypkm m LEFT JOIN pkm_pkmdata p ON m.id = p.id WHERE m.uid = ' . $trainer['uid'] . ' AND m.place IN (1, 2, 3, 4, 5, 6) AND m.id != 0 ORDER BY m.place ASC');

            $i = 1;

            while($info = DB::fetch($query)) {

                if($i === 1 && $info['hp'] < 1)

                    $return['js'] = 'DISABLE.BATTLEEND = true;';

                $info['height']         = $info['height'] / 10;
                $info['weight']         = $info['weight'] / 10;
                $info['gendersign']     = Obtain::GenderSign($info['gender']);
                $info['move']           = unserialize($info['move']);
                $info['newmove']        = !empty($info['newmove']) ? unserialize($info['newmove']) : [];
                Battle::$pokemon[$i]    = [$info, Battle::GenerateBattleData($info['pid'])];
                Battle::$pokemon[$i][0] = array_merge(Battle::$pokemon[$i][0],
                    Obtain::Stat(
                        Battle::$pokemon[$i][0]['level'],
                        Battle::$pokemon[$i][0]['bs'],
                        Battle::$pokemon[$i][0]['iv'],
                        Battle::$pokemon[$i][0]['ev'],
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

            DB::query('INSERT INTO pkm_battlefield (uid) VALUES (' . $trainer['uid'] . ') ON DUPLICATE KEY UPDATE weather = 0, trkroom = 0, gravity = 0');
            DB::query('UPDATE pkm_trainerdata SET inbtl = 1 WHERE uid = ' . $trainer['uid']);

            Battle::$pokemon[0] = [
                Pokemon::Generate($appearpkm['id'], $trainer['uid'], [
                    'mtplace' => $mapid,
                    'mtlevel' => $appearpkm['level'],
                    'wild'    => 1
                ]),
                Battle::GenerateBattleData()
            ];

            Battle::$pokemon[0][0]               = array_merge(
                Battle::$pokemon[0][0], Obtain::Stat(
                Battle::$pokemon[0][0]['level'],
                Battle::$pokemon[0][0]['bs'],
                Battle::$pokemon[0][0]['iv'],
                Battle::$pokemon[0][0]['ev'],
                Battle::$pokemon[0][0]['nature'],
                Battle::$pokemon[0][0]['hp']
            )
            );
            Battle::$pokemon[0][0]['gendersign'] = Obtain::GenderSign(Battle::$pokemon[0][0]['gender']);
            Battle::$pokemon[0][0]['move']       = unserialize(Battle::$pokemon[0][0]['move']);

            ksort(Battle::$pokemon);

            Battle::$pokemon[7] = Battle::$pokemon[8] = Battle::GenerateFieldData();

            Battle::ReorderPokemon();

            Battle::WriteBattleData($trainer['uid'], Battle::$pokemon);

            Battle::$report = '野生的' . Battle::$pokemon[0][0]['name'] . '出现了！<br>' . $trainer['username'] . '派出了' . Battle::$pokemon[1][0]['name'] . '！<br>';

            if(Pokemon::Register($appearpkm['id']) === FALSE)

                Battle::$report .= Battle::$pokemon[0][0]['name'] . '被登记在了图鉴中。<br>';

            Battle::$report .= '<br>';


            /*
                Initiate battle layer
                display pokemon info
            */

            $return['js'] = 'initbattle();$(\'#sbj-oppo\').html(\'' . Battle::$pokemon[0][0]['name'] . Battle::$pokemon[0][0]['gendersign'] . ' Lv. ' . Battle::$pokemon[0][0]['level'] . ' ' . Obtain::StatusIcon(Battle::$pokemon[0][0]['status']) . '<div class="bar"><div class="hp" style="width:' . Battle::$pokemon[0][0]['hpper'] . '%"></div><div class="value">' . Battle::$pokemon[0][0]['hp'] . '/' . Battle::$pokemon[0][0]['maxhp'] . '</div></div><div class="sprite"><img src="' . Obtain::Sprite('pokemon', 'png', Battle::$pokemon[0][0]['imgname']) . '"></div>\').show().animate({left:\'750px\'}, 3000);
            $(\'#sbj-self\').show().html(\'' . Battle::$pokemon[1][0]['name'] . Battle::$pokemon[1][0]['gendersign'] . ' Lv. ' . Battle::$pokemon[1][0]['level'] . ' ' . Obtain::StatusIcon(Battle::$pokemon[1][0]['status']) . '<div class="bar"><div class="hp" style="width:' . Battle::$pokemon[1][0]['hpper'] . '%"></div><div class="value">' . Battle::$pokemon[1][0]['hp'] . '/' . Battle::$pokemon[1][0]['maxhp'] . '</div></div><div class="sprite"><img src="' . Obtain::Sprite('pokemon', 'png', Battle::$pokemon[1][0]['imgname'], 0, 1) . '"></div>\').show().animate({left:\'105px\'}, 2000);$(\'#btl-report\').show().html(\'' . Battle::$report . '\');';


            /*
                Generate pokemon move info
            */

            $tmp = '';

            foreach(Battle::$pokemon[1][0]['move'] as $val) {

                $tmp .= '<div data-mid="' . $val[0] . '"' . (($val[1] <= 0) ? ' class="disabled"' : '') . '>' . $val[2] . ' <em>' . $val[1] . '/' . ($val[3] + $val[3] * $val[4] / 5) . '</em></div>';

            }

            $return['js'] .= '$(\'#obj-move\').html(\'' . $tmp . '\');';


            /*
                Generate item info
            */

            $tmp = '';

            foreach(Obtain::BagItem('(i.type = 1 AND i.usable = 0 OR i.type = 4 AND i.btlefct != \'\' OR i.effect != \'\')', 'i.type ASC', 'GROUPED:type') as $val) {

                $tmp .= '<strong>' . Obtain::ItemClassName($val[0]['type']) . '</strong><ul>';

                foreach($val as $valb) {

                    $tmp .= '<li data-iid="' . $valb['iid'] . '" title="' . $valb['name'] . '（余' . $valb['num'] . '个）：' . $valb['dscptn'] . '"><img src="' . Obtain::Sprite('item', 'png', 'item_' . $valb['iid']) . '"></li>';

                }

                $tmp .= '</ul><br clear="both">';

            }

            $return['js'] .= '$(\'#lyr-item\').html(\'' . (!empty($tmp) ? $tmp : '你的背包空空如也！') . '\');';

            /*
                Generate pokemon info
            */

            $tmp = '';

            foreach(Battle::$pokemon as $key => $val) {

                if($key < 2 || $key > 6 || $val[0]['hp'] < 1) continue;

                $tmp .= '<li data-pid="' . $val[0]['pid'] . '"><img src="' . ROOTIMG . '/pokemon-icon/' . $val[0]['id'] . '.png"> ' . $val[0]['name'] . ' Lv.' . $val[0]['level'] . '</li>';

            }

            $return['js'] .= '$(\'#lyr-pokemon\').html(\'' . (!empty($tmp) ? '<ul>' . $tmp . '</ul>' : '没有可战斗的精灵。') . '\');';


            define('MODE', FALSE);

            if(MODE === 1) {

                $sql   = [];
                $query = DB::query('SELECT pid, id, level FROM pkm_mypkm');
                while($info = DB::fetch($query)) {
                    $move   = [];
                    $queryb = DB::query('SELECT pm.mid, md.pp, md.name FROM pkm_pkmmove pm LEFT JOIN pkm_movedata md ON md.mid = pm.mid WHERE pm.way = 1 AND pm.id = ' . $info['id'] . ' AND pm.level <= ' . $info['level'] . ' ORDER BY pm.level DESC LIMIT 4');
                    while($infob = DB::fetch($queryb)) {
                        $move[] = [$infob['mid'], $infob['pp'], $infob['name'], $infob['pp'], 0];
                    }
                    $sql[] = '(' . $info['pid'] . ', \'' . serialize($move) . '\')';
                }
                DB::query('INSERT INTO pkm_mypkm (pid, move) VALUES ' . implode(',', $sql) . ' ON DUPLICATE KEY UPDATE move = VALUES(move)');
            } elseif(MODE === 2) {
                $query = DB::query('SELECT mid, pp, name FROM pkm_movedata');
                $move  = [];
                while($info = DB::fetch($query)) {
                    $move[$info['pid']] = $info;
                }
                $query = DB::query('SELECT pid, move FROM pkm_mypkm');
                $sql   = [];
                while($info = DB::fetch($query)) {
                    $info['move'] = unserialize($info['move']);
                    foreach($info['move'] as $key => $val) {
                        $info['move'][$key] = [$val[0], $move[$val[0]]['pp'], $move[$val[0]]['name'], $move[$val[0]]['pp'], 0];
                    }
                    $sql[] = '(' . $info['pid'] . ', \'' . serialize($info['move']) . '\')';
                }
                DB::query('INSERT INTO pkm_mypkm (pid, move) VALUES ' . implode(',', $sql) . ' ON DUPLICATE KEY UPDATE move = VALUES(move)');
            }


        } else {

            $return['console'] .= 'No pokemon in party have ability to battle! ';

        }

        $return['console'] .= 'Walk to (' . $x . ', ' . $y . ')';

        break;
}

END: