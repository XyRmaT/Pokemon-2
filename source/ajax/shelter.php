<?php

switch($process) {
	case 'claim-pokemon':

		$info = DB::fetch_first('SELECT pkm_id, nat_id, initial_user_id
                                 FROM pkm_mypkm
                                 WHERE pkm_id = ' . intval($_GET['pkm_id']) . ' AND location = ' . LOCATION_SHELTER);

		if(empty($info)) {
			$return['msg'] = General::getText('shelter_already_claimed');
		} elseif($trainer['currency'] - $system['costs']['shelter_claim'] < 0) {
			$return['msg'] = General::getText('unpaid', [$system['currency_name']]);
		} elseif(($location = Obtain::DepositBox($trainer['user_id'])) === FALSE) {
			$return['msg'] = General::getText('locations_full');
		} else {
            App::CreditsUpdate($trainer['user_id'], -$system['costs']['shelter_claim']);
            PokemonGeneral::moveLocation($info['pkm_id'], $location, ['user_id' => $trainer['user_id']]);
            PokemonGeneral::registerPokedex($info['nat_id'], $trainer['user_id'], TRUE);

            Trainer::AddExp($trainer, $info['initial_user_id'] != $trainer['user_id'] ? $system['adding_exp']['shelter_claim'] : 0, TRUE);
            $return['msg']     = General::getText('shelter_claimed');
        }

		break;
}

include ROOT . '/source/index/shelter.php';
$return['data'] = ['pokemon' => $pokemon, 'eggs' => $eggs];