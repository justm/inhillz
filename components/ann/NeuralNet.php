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
     * @var array Vstupy pre aktuálny výpočet 
     */
    private $inputs = [];
    
    /**
     * @var array výsledok výpočtu count($outputs) == $numOutputs
     */
    private $outputs = [];
    
    /**
     * @var array Viacrozmerne pole s výstupmi pre každú vrstvu
     */
    private $partialOutputs = [];
    
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
            
            // Prva skryta vrstva
            array_push($this->layers, new NeuronLayer($this->numNeuronsPerLayer, $this->numInputs));
    
            for ($i = 0; $i < $this->numHiddenLayers - 1; $i) { //dalsie skryte vrstvy
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
	
	for ($i = 0; $i < $this->numHiddenLayers + 1; $i++) { //všetky vrstvy

            for ($j = 0; $j < $this->layers[$i]->numNeurons; $j++) { //všetky neurony
                
                for ($k = 0; $k < $this->layers[$i]->neurons[$j]->numInputs; $k++) { //všetky váhy
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
	
	for ($i = 0; $i <= $this->numHiddenLayers; $i++) { //všetky vrstvy

            for ($j = 0; $j < $this->layers[$i]->numNeurons; $j++) { //všetky neurony
                
                for ($k = 0; $k < $this->layers[$i]->neurons[$j]->numInputs; $k++) { //všetky váhy
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
        
	for ($i = 0; $i <= $this->numHiddenLayers; $i++) { //všetky vrstvy

            for ($j = 0; $j < $this->layers[$i]->numNeurons; $j++) { //všetky neurony
                
                for ($k = 0; $k < $this->layers[$i]->neurons[$j]->numInputs; $k++) { //všetky váhy
                    $this->layers[$i]->neurons[$j]->weights[$k] = $weights[$weight_iter++];
                }
            }
	}
    }
    
    /**
     * 
     * @param array $deltas
     * @param float $learnRate
     */
    private function updateWeights($deltas, $learnRate){
        
	for ($i = 0; $i <= $this->numHiddenLayers; $i++) { //všetky vrstvy

            for ($j = 0; $j < $this->layers[$i]->numNeurons; $j++) { //všetky neurony
                
                for ($k = 0; $k < ($i==0? $this->numInputs : $this->layers[$i-1]->numNeurons); $k++) { //všetky v8hy                  
                    $weightDelta = $learnRate * $deltas[$i][$j] * ($i == 0 ? $this->inputs[$k] : $this->partialOutputs[$i-1][$k]);
                    $this->layers[$i]->neurons[$j]->weights[$k] += $weightDelta;
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
        $this->partialOutputs = $this->outputs = $outputs = [];
        $this->inputs = $inputs = array_values($in); //remove keys
        
	if (count($inputs) != $this->numInputs){
            return $outputs;
        }
	
	// Všetky vrstvy
	for ( $i = 0; $i < $this->numHiddenLayers + 1; $i++) {	
            
            if ( $i > 0 ) {
               $inputs = $outputs; //vystup predchadzajucej vrstvy je vstup do aktualnej
            }

            $outputs     = [];
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
            
            $this->partialOutputs[] = $outputs;
	}

        $this->outputs = $outputs;
	return $outputs;
    }
    
    /**
     * Trénovanie siete
     * @param array $trainData
     * [
     *    0 => ['inputs' => [], 'outputs' => []],
     *    1 => ['inputs' => [], 'outputs' => []],
     *    ...
     *    n => ['inputs' => [], 'outputs' => []],
     * ]
     */
    public function train($trainData){
        
        $epoch = 0;
        
        echo '<pre>';
        $log = fopen(__DIR__ . '/mse.log', 'w');
        
        echo 'Net: ';
        var_dump($this->layers);
        
        while($epoch < Params::$numEpochs){
            
            $mse = $this->meanSquaredError($trainData);
            
            if($mse < Params::$mseTarget) {
                break;
            }
            
            foreach ($trainData as $sample) {
                $inputs  = $sample['inputs'];
                $desired = $sample['outputs'];
                $outputs = $this->compute($inputs); 

                echo 'Inputs: ';
                var_dump($inputs);
                
                echo 'Desired: ';
                var_dump($desired);
                
                echo 'Outputs: ';
                var_dump($outputs);
                
                $errors  = $this->computeDeltas($outputs, $desired);
                $this->updateWeights($errors, Params::$learnRate);
                
                echo 'Net: ';
                var_dump($this->layers);
                exit();
            }
            
            $epoch++;
            
            fwrite($log, $mse . PHP_EOL);
        }
        
        var_dump($this->getWeights());
    }
    
    /**
     * Back-propagate
     * 
     * @param array $outputs Výstupy siete
     * @param array $desired Trénovacie (požadované) výstupy
     * @return array Chyby po vrstvach pre kazdy neuron
     */
    private function computeDeltas($outputs, $desired){
        
        $deltas = [];
        $l      = count($this->layers) - 1;
        
        for($k = 0; $k < count($outputs); $k++){ 
            $derivative     = (1 - $outputs[$k]) * $outputs[$k]; //log sigmoid
            $deltas[$l][$k] = $derivative * ($desired[$k] - $outputs[$k]); 
        } // called "delta rule" as well
                
        for($l = count($this->layers)-2; $l >= 0; $l--){ //hidden layers

            for($i = 0; $i < $this->layers[$l]->numNeurons; $i++){
                $sum = 0.0;

                for($j = 0; $j < $this->layers[$l+1]->numNeurons; $j++){
                    $sum += $this->layers[$l+1]->neurons[$j]->weights[$i] * $deltas[$l+1][$j];
                }
                $deltas[$l][$i] = $sum * (1 - $this->partialOutputs[$l][$i]) * $this->partialOutputs[$l][$i]; 
            }
        }
        
        return $deltas;
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
        $inputs = []; 
        $desired = []; 

        foreach ($trainData as $sample) {
            $inputs  = $sample['inputs'];
            $desired = $sample['outputs'];

            $yValues = $this->compute($inputs); // compute output using current weights

            for ($j = 0; $j < $this->numOutputs; ++$j) {
                $sumSquaredError += pow($desired[$j] - $yValues[$j], 2);
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
