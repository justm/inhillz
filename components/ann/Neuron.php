<?php

namespace inhillz\components\ann;

use inhillz\components\Helper;

/**
 * Neuron
 * 
 * @namespace  inhillz\components\ann; 
 * @author     Matus Macak <matus.macak@orchidsphere.com>
 * @link       http://ride.inhillz.com/
 * @version    2.0
 * 
 */
class Neuron {

    /**
     * @var int Počet vstupov do neurónu
     */
    public $numInputs = 0;
    
    /**
     * @var array Váha pre každý vstup do neurónu
     */
    public $weights = [];
    
    /**
     * Konštruktor
     * @param int $numImputs
     */
    public function __construct($numImputs) {
        
        $this->numInputs = $numImputs; //Váha pre bias - preto +1
        
        for($i = 0; $i <= $this->numInputs; $i++){
            array_push($this->weights, Helper::random(-1,1)); //Inicializácia na náhodných váh
        }
    }
}