<?php

switch($process) {
	case 'claim-pokemon':

		$info = DB::fetch_first('SELECT pkm_id, nat_id, initial_user_id
                                 FROM pkm_mypkm
                                 WHERE pkm_id = ' . intval($_GET['pkm_id']) . ' AND location = ' . LOCATION_SHELTER);

		if(empty($info)) {
			$return['msg'] = Obtain::Text('shelter_already_claimed');
		} elseif($trainer['currency'] - $system['costs']['shelter_claim'] < 0) {
			$return['msg'] = Obtain::Text('unpaid', [$system['currency_name']]);
		} elseif(($location = Obtain::DepositBox($trainer['user_id'])) === FALSE) {
			$return['msg'] = Obtain::Text('locations_full');
		} else {
            App::CreditsUpdate($trainer['user_id'], -$system['costs']['shelter_claim']);
            Pokemon::MoveLocation($info['pkm_id'], $location, ['user_id' => $trainer['user_id']]);
            Pokemon::DexRegister($info['nat_id'], TRUE);

            Trainer::AddExp($trainer, $info['initial_user_id'] != $trainer['user_id'] ? $system['adding_exp']['shelter_claim'] : 0, TRUE);
            $return['msg']     = Obtain::Text('shelter_claimed');
        }

		break;
}

include ROOT . '/source/index/shelter.php';
$return['data'] = ['pokemon' => $pokemon, 'eggs' => $eggs];