<?php

namespace inhillz\components\ann;

/**
 * Genome
 * 
 * @namespace  inhillz\components\ann; 
 * @author     Matus Macak <matus.macak@orchidsphere.com>
 * @link       http://ride.inhillz.com/
 * @version    2.0
 */
class Genome {

    /**
     * @var array 
     */
    public $vecWeights = []; 

    /**
     * @var float 
     */
    public $dFitness = 0.0;

    /**
     * Konštruktor
     * @param array $w
     * @param float $f
     */
    public function __construct ($w = [], $f = 0){
        
        $this->vecWeights = $w;
        $this->dFitness   = $f;
    }
}
