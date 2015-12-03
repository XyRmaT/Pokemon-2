<?php

switch($_GET['process']) {

	case 'obtain':

		$sid  = intval($_GET['sid']); // starter's id
		$sarr = [1, 4, 7, 152, 155, 158, 252, 255, 258, 387, 390, 393, 495, 498, 501];

		if(!empty($trainer['sttchk'])) $return['msg'] = '真贪心！';
		elseif(!in_array($sid, $sarr)) $return['msg'] = '你只能从那几只中选哦！';

		else {

			Kit::Library('class', ['pokemon', 'obtain']);

			Pokemon::Generate($sid, $trainer['uid'], ['met_location' => 600]);

			DB::query('UPDATE pkm_trainerdata SET sttchk = 1 WHERE uid = ' . $trainer['uid']);

			$return['js'] = 'window.location.reload();';

		}

		break;

}

?>