<?php

namespace inhillz\components\ann;

/**
 * NeuronLayer - Neurónová vrstva
 * 
 * @namespace  inhillz\components\ann; 
 * @author     Matus Macak <matus.macak@orchidsphere.com>
 * @link       http://ride.inhillz.com/
 * @version    2.0
 * 
 */
class NeuronLayer {
    
    /**
     * @var int Počet neurónov vo vrstve 
     */
    public $numNeurons = 0;
    
    /**
     * @var array of Neuron - Neurony vo vrstve
     */
    public $neurons = [];
    
    /**
     * Konštruktor
     * @param int $numNeurons
     * @param int $numInputsPerNeuron
     */
    public function __construct($numNeurons, $numInputsPerNeuron) {
        
        $this->numNeurons = $numNeurons;
        
        for ($i = 0; $i < $numNeurons; $i++) {
            array_push($this->neurons, new Neuron($numInputsPerNeuron));
        }
    }
}
