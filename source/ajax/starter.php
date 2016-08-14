<?php

switch($process) {

    case 'claim-pokemon':

        $nat_id = intval($_GET['nat_id']);

        if(!empty($trainer['has_starter'])) {
            $return['msg'] = Obtain::Text('starter_greedy');
        } elseif(!in_array($nat_id, $system['starter'])) {
            $return['msg'] = Obtain::Text('starter_invalid_pokemon');
        } else {
            try {
                Pokemon::Generate($nat_id, $trainer['user_id'], [
                    'met_location' => 600,
                    'met_level'    => 5,

                ]);
                DB::query('UPDATE pkm_trainerdata SET has_starter = 1 WHERE user_id = ' . $trainer['user_id']);
                $return['js'] = 'window.location.reload();';
            } catch(Exception $e) {
                $return['msg'] = $e->getMessage();
            }
        }

        break;

}