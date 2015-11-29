<?php

Kit::Library('class', array('battle', 'pokemon'));

$mapid = isset($_GET['mpid']) ? intval($_GET['mpid']) : 1;

/**
 * Fetch the available map data which met the criteria of:
 *      - In a certain time period
 *      ...
 */

$map = DB::fetch_first('SELECT mpid FROM pkm_mapdata
                        WHERE mpid = ' . $mapid . ' AND (timestt < ' . $_SERVER['REQUEST_TIME'] . ' AND timefns > ' . $_SERVER['REQUEST_TIME'] . ' OR timefns = 0 AND timestt < ' . $_SERVER['REQUEST_TIME'] . ')');


if(!$map) {

    Kit::ShowMessage('您要到哪里呀？');

} elseif(!$user['inbtl']) {

    $tmp    = [];
    $query  = DB::query('SELECT id, levelmin, levelmax, rate
                         FROM pkm_encounterdata
                         WHERE mpid = ' . $mapid . ' AND timefrom < ' . $_SERVER['REQUEST_TIME'] . ' AND (timeto = 0 OR timeto > ' . $_SERVER['REQUEST_TIME'] . ') AND qty != 0');


    while($info = DB::fetch($query))

        $tmp[] = $info;


    if(!$tmp) {

        $return['msg'] = '一只精灵都没看到……';

        break;

    }

    $i = 0;

    for(;;) {

        $appearpkm = $tmp[array_rand($tmp)];

        if(rand(1, 100) <= $appearpkm['rate']) {

            $appearpkm['level'] = rand($appearpkm['levelmin'], $appearpkm['levelmax']);

            break;

        }

        if($i > 50) {

            $return['msg'] = '似乎有精灵的黑影闪过！';

            break;

        }

        ++$i;

    }

    DB::query('INSERT INTO pkm_battlefield (uid) VALUES (' . $_G['uid'] . ') ON DUPLICATE KEY UPDATE weather = 0, trkroom = 0, gravity = 0');

    Battle::$pokemon[0] = array(
        Pokemon::Generate($appearpkm['id'], $_G['uid'], array(
            'mtplace'    => $map['mpid'],
            'mtlevel'    => $appearpkm['level'],
            'wild'        => 1
        )),
        Battle::GenerateBattleData()
    );

    $query = DB::query('SELECT m.pid, m.id, m.nickname, m.gender, m.pv, m.iv, m.ev, m.nature, m.level, m.crritem, m.hpns, m.move, m.abi, m.hp, m.status, m.imgname, p.bs, p.type, p.typeb FROM pkm_mypkm m LEFT JOIN pkm_pkmdata p ON m.id = p.id WHERE m.uid = ' . $_G['uid'] . ' AND m.place IN (1, 2, 3, 4, 5, 6) AND m.id != 0 ORDER BY m.place ASC');

    $hp    = 0;
    $i    = 1;

    while($info = DB::fetch($query)) {

        $hp                        += $info['hp'];
        Battle::$pokemon[$i]    = array($info, Battle::GenerateBattleData($info['pid']));

        ++$i;

    }

    if(empty(Battle::$pokemon) || $hp <= 0) {

        $return['msg'] = '您没有可以参战的精灵！';

        break;

    }

debuginfo();
    echo '<pre><img src="' . Obtain::Sprite('pokemon', 'png', Battle::$pokemon[0][0]['imgname']) . '"><img src="' . Obtain::Sprite('pokemon', 'png', Battle::$pokemon[1][0]['imgname']) . '"><br>Processed in ' . $_G['debuginfo']['time']  . 'second(s), ' . $_G['debuginfo']['queries'] . ' queries.';
    print_r(Battle::$pokemon[0][0]);
exit;

    Battle::$turn    = 'firstTurn';
    Battle::$field   = array('000000000', '000000000');
    Battle::$report .= '野生的' . Battle::$pokemon[0][0]['name'] . '出现了！<br>';

    unset($tmp, $query, $appearpkm);


    /*
    $query = DB::query('SELECT type, class, power, acc, prio, freq, pp, freq, critRt, effect, btlEfct FROM pkm_movedata WHERE mid IN (' . $midB . ((!empty($mid)) ? ', ' . $mid : '') . ')');
    $i = 0;
    while($tempVar = DB::fetch_array($query)) {
        Battle::moveData[$i] = array_merge(Battle::moveData[$i], $tempVar);;
        ++$i;
    }*/
#echo'<pre>';print_r(Battle::pkmData);echo'</pre>';
#echo'<pre>';var_dump(Battle::reorderPkm());print_r(Battle::pkmData);echo'</pre>';
    /*if(!(Battle::pkmData = Battle::reorderPkm(Battle::pkmData))) {
        showError(2);
    }
    Battle::pkmMove = array(unserialize(Battle::pkmData[0][0]['move']), unserialize(Battle::pkmData[1][0]['move']));
    print_r(Battle::pkmMove);

    foreach(Battle::pkmMove as $key => $tempVar) {
        switch($key) {
            case 0:    
                Battle::moveData[$key] = $tempVar[array_rand($tempVar)];
                $midB = Battle::moveData[$key][0];
            break;
            default:
                foreach($tempVar as $tempVarB) {
                    if(!empty($mid) && $tempVarB[0] == $mid) {
                        $checked = 1;
                    }
                    Battle::moveData[$key] = $tempVarB;
                }
            break;
        }
    }

    unset($tempVar, $tempVarB, $key, $checked, $instruct, $mid, $midB, $pkmMove, $i, $query); // Unset unecessary variables for memories
    Battle::end();
    DB::query('UPDATE pkm_trnrdata SET inBtl = 1 WHERE uid = ' . $discuz_uid);*/
}

/*
// Battle finished

// Exp update
// Temporary Note: insert into test_tbl (id,dr) values  (1,'2'),(2,'3'),...(x,'y') on duplicate key update dr=values(dr);
$i = $j = $expSharePkmCount = $ptcptPkmCount = 0;
$sqlPart = $pkmStr = '';
$pkmListA = array();
foreach($pokemon as $key => $value) {
    if($key == 0 || empty($value[1][4]) && $value[0]['crrItem'] != '学习装置') {
        continue;
    }
    if($value[0]['hp'] > 0) {
        $pkmStr .= ($i === 0) ? $value[0]['id'] : ',' . $value[0]['id'];
        if($value[1][4] == 1) {
            ++$ptcptPkmCount;
        }
        if($value[0]['crrItem'] == '学习装置') {
            ++$expSharePkmCount;
        }
        $pkmListA[] = array($value[0]['level'], $pokemon[0][0]['level'], $value[0]['oOUid'], $discuz_uid, $value[0]['crrItem']);
        ++$i;
    }
}
$query = DB::query("SELECT baseEXP FROM pkm_pkmdata WHERE id IN (" . $pkmStr . ")");
while($pkmListB = DB::fetch_array($query)) {
    if($ptcptPkmCount
    $exp = gainExp($pkmListB['baseEXP'], $pkmListA[$j][0], $pkmListA[$j][1], $pkmListA[$j][2], $pkmListA[$j][3], $pkmListA[$j][4], $pkmCount);
    $sqlPart .= ($j === 0) ? '(' . $value[0]['pid'] . ',' . $value[0]['exp'] + $exp . ')' : ',()';
    $j++;
// In process mark.
unset($pkmListA, $pkmListB
*/
?>