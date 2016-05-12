<?php

namespace inhillz\components\ann;

/**
 * NeuralNet - Neurónová sieť
 * 
 * @namespace  inhillz\components\ann; 
 * @author     Matus Macak <matus.macak@orchidsphere.com>
 * @link       http://ride.inhillz.com/
 * @version    2.0
 * 
 */
class NeuralNet {
    
    /**
     * @var int Počet vstupov siete 
     */
    private $numInputs;

    /**
     * @var int Počet výstupov siete 
     */
    private $numOutputs;

    /**
     * @var int Počet skrytých vrstiev 
     */
    private $numHiddenLayers;

    /**
     * @var int Počet neurónov v skrytej vrstve
     */
    private $numNeuronsPerLayer;

    /**
     * @var array Pole všetkých vrstiev siete aj s výstupnou vrstvou
     */
    private $layers = [];

    /**
     * Konštruktor
     */
    public function __construct() {
	
        $this->numInputs          = Params::$iNumInputs;
	$this->numOutputs         = Params::$iNumOutputs;
	$this->numHiddenLayers    = Params::$iNumHidden;
	$this->numNeuronsPerLayer = Params::$iNumNeuronsPerLayer;

	$this->createNet();
    }

    public function createNet(){
        
	if ($this->numHiddenLayers > 0) {
            
            // Prva vrstva
            array_push($this->layers, new NeuronLayer($this->numNeuronsPerLayer, $this->numInputs));
    
            for ($i = 0; $i < $this->numHiddenLayers - 1; $i) {
                array_push($this->layers, new NeuronLayer($this->numNeuronsPerLayer, $this->numNeuronsPerLayer));
            }

            // Vystupna vrstva
            array_push($this->layers, new NeuronLayer($this->numOutputs, $this->numNeuronsPerLayer));
	}
        else {
            // Vystupna vrstva
            array_push($this->layers, new NeuronLayer($this->numOutputs, $this->numInputs));
        }
    }

    /**
     * Vráti váhy neurónovej siete
     * @return array
     */
    public function getWeights(){

        $weights = [];
	
	//všetky vrstvy
	for ($i = 0; $i < $this->numHiddenLayers + 1; $i++) {

            //všetky neurony
            for ($j = 0; $j < $this->layers[$i]->numNeurons; $j++) {
                
                //všetky váhy
                for ($k = 0; $k < $this->layers[$i]->neurons[$j]->numInputs; $k++) {
                    array_push($weights, $this->layers[$i]->neurons[$j]->weights[$k]);
                }
            }
	}

	return $weights;
    }
    
    /**
     * Vráti celkovú počet potrebný váh v sieti
     * @return float
     */
    public function getNumberOfWeights(){
        
        $num_weights = 0;
	
	//všetky vrstvy
	for ($i = 0; $i < $this->numHiddenLayers + 1; $i++) {

            //všetky neurony
            for ($j = 0; $j < $this->layers[$i]->numNeurons; $j++) {
                
                //všetky váhy
                for ($k = 0; $k < $this->layers[$i]->neurons[$j]->numInputs; $k++) {
                    $num_weights++;
                }
            }
	}
        
        return $num_weights;
    }
    
    /**
     * Nahradí pôvodné váhy novými
     * @param array $weights Jednorozmerné pole
     */
    public function putWeights($weights){
        
        $weight_iter = 0;
        
        //všetky vrstvy
	for ($i = 0; $i < $this->numHiddenLayers + 1; $i++) {

            //všetky neurony
            for ($j = 0; $j < $this->layers[$i]->numNeurons; $j++) {
                
                //všetky váhy
                for ($k = 0; $k < $this->layers[$i]->neurons[$j]->numInputs; $k++) {
                    $this->layers[$i]->neurons[$j]->weights[$k] = $weights[$weight_iter++];
                }
            }
	}
    }
    
    /**
     * Vypočíta výstup so vstupov
     * @param array $in
     * @return array
     */
    public function compute($in){
        
        // Výsledok = výstup pre každú vrstvu
        $outputs = [];
        $inputs  = array_values($in); //remove keys
        
	if (count($inputs) != $this->numInputs){
            return $outputs;
        }
	
	// Všetky vrstvy
	for ( $i = 0; $i < $this->numHiddenLayers + 1; $i++) {	
            
            if ( $i > 0 ) {
                $inputs = $outputs;
            }

            $outputs = [];
            
            $weight_iter = 0;

            // Všetky neuróny
            for ($j=0; $j < $this->layers[$i]->numNeurons; $j++) {
                
                $netinput   = 0;
                $num_imputs = $this->layers[$i]->neurons[$j]->numInputs;
                
                // Všetky váhy
                for ($k = 0; $k < $num_imputs - 1; $k++) {
                    
                    $netinput += $this->layers[$i]->neurons[$j]->weights[$k] * $inputs[$weight_iter++];
                }
                $netinput += $this->layers[$i]->neurons[$j]->weights[$num_imputs-1] * Params::$dBias;

                // Filter sigmoid
                array_push($outputs, $this->sigmoid($netinput, Params::$dActivationResponse));

                $weight_iter = 0;
            }
	}

	return $outputs;
    }
    
    public function train($trainData){
        
        $epoch = 0;
        $learnRate = Params::$learnRate;
        
        while($epoch < Params::$numEpochs){
            
            $mse = $this->meanSquaredError($trainData);
            var_dump($mse);
            break;
            $epoch++;
        }
    }
    
    /**
     * Počíta mean squared error na jednej dávke trénovacích dát
     * @param array $trainData
     * [
     *    0 => ['inputs' => [], 'outputs' => []],
     *    1 => ['inputs' => [], 'outputs' => []],
     *    ...
     *    n => ['inputs' => [], 'outputs' => []],
     * ]
     * @return float
     */
    private function meanSquaredError($trainData) {

        $sumSquaredError = 0.0;
        $xValues = []; 
        $tValues = []; 

        for ($i = 0; $i < count($trainData); ++$i) {

                $xValues = $trainData[$i]['inputs'];
                $tValues = $trainData[$i]['outputs'];
                
                $yValues = $this->compute($xValues); // compute output using current weights

                for ($j = 0; $j < $this->numOutputs; ++$j) {
                    $err = $tValues[$j] - $yValues[$j];
                    $sumSquaredError += $err * $err;
                }
        }

        return $sumSquaredError / count($trainData);
    }
    
    /**
     * Sigmoid
     * @return float
     */
    public function sigmoid($activation, $response){
        
        return ( 1 / ( 1 + exp(-$activation / $response)));
    }
}
