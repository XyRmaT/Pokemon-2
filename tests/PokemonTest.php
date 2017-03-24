<?php


include '../include/class/PokemonGeneral.php';

class PokemonTest extends PHPUnit_Framework_TestCase {

    public function testDevolution () {
        $this->assertEquals(1, PokemonGeneral::getDevolution(3));
        $this->assertEquals(2, PokemonGeneral::getDevolution(3, TRUE));
        $this->assertEquals(1, PokemonGeneral::getDevolution(1));
    }

}
