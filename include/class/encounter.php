<?php

class Encounter {

    private $map_id = 0;

    public function generate($map_id) {
        return $this->setMap($map_id)->generatePokemon();
    }

    public function setMap($map_id) {
        $this->map_id = $map_id;
        return $this;
    }

    private function fetchEncounterTable() {
        $query      = DB::query('SELECT nat_id, rate, level_min, level_max FROM pkm_encounterdata WHERE map_id = ' . $this->map_id . ' AND quantity != 0 AND time_start < ' . $_SERVER['REQUEST_TIME'] . ' AND (time_end = 0 OR time_end > ' . $_SERVER['REQUEST_TIME'] . ')');
        $rate_total = 0;
        $enc_table  = [];
        while($info = DB::fetch($query)) {
            $info['rate'] = $rate_total += $info['rate'];
            $enc_table[]  = $info;
        }

        $rate_alloc = mt_rand(1, $rate_total);
        foreach($enc_table as $pokemon) {
            if($rate_alloc <= $pokemon['rate']) return $pokemon;
        }
        return FALSE;
    }

    public function generatePokemon() {
        global $trainer;
        $pokemon = $this->fetchEncounterTable();
        return !$pokemon ? FALSE : Pokemon::Generate($pokemon['nat_id'], $trainer['user_id'], [
            'met_location' => $this->map_id,
            'met_level'    => mt_rand($pokemon['level_min'], $pokemon['level_max']),
            'is_wild'      => TRUE
        ]);
    }

}