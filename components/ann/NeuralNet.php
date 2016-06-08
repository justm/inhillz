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
     * Priemer z dát, podľa vstupného/výstupného atribútu siete
     * Používa sa pri normalizácii
     * @var array 
     */
    private $mean = [];
    
    /**
     * Smerodajná odchýlka z dát, podľa vstupného/výstupného atribútu siete
     * Používa sa pri normalizácii
     * @var array 
     */
    private $deviation = [];
    
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
	
        $this->numInputs          = Params::$numInputs;
	$this->numOutputs         = Params::$numOutputs;
	$this->numHiddenLayers    = count(Params::$hiddenLayers);
	$this->numNeuronsPerLayer = Params::$hiddenLayers;

	$this->createNet();
    }

    /**
     * Vytvorenie neurónovej siete
     */
    public function createNet(){
        
	if ($this->numHiddenLayers > 0) {
            
            // Prva skryta vrstva
            array_push($this->layers, new NeuronLayer($this->numNeuronsPerLayer[0], $this->numInputs));
    
            for ($i = 0; $i < $this->numHiddenLayers - 1; $i++) { //dalsie skryte vrstvy
                array_push($this->layers, new NeuronLayer($this->numNeuronsPerLayer[$i], $this->numNeuronsPerLayer[$i]));
            }

            // Vystupna vrstva
            array_push($this->layers, new NeuronLayer($this->numOutputs, $this->numNeuronsPerLayer[$i]));
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
        
        // Váhy vstupov
	for ($i = 0; $i <= $this->numHiddenLayers; $i++) { //všetky vrstvy

            for ($j = 0; $j < $this->layers[$i]->numNeurons; $j++) { //všetky neurony
                
                for ($k = 0; $k < ($i==0? $this->numInputs : $this->layers[$i-1]->numNeurons); $k++) { //všetky váhy                  
                    $weightDelta = $learnRate * $deltas[$i][$j] * ($i == 0 ? $this->inputs[$k] : $this->partialOutputs[$i-1][$k]);
                    $this->layers[$i]->neurons[$j]->weights[$k] -= $weightDelta;
                }
            }
	}
        
        // Váhy pre bias
        for ($i = 0; $i <= $this->numHiddenLayers; $i++) { //všetky vrstvy
            
            for ($j = 0; $j < $this->layers[$i]->numNeurons; $j++) { //všetky neurony
                $b = $this->layers[$i]->neurons[$j]->numInputs; //bias index - má vždy poslednú váhu
                $this->layers[$i]->neurons[$j]->weights[$b] -=  $learnRate * $deltas[$i][$j]; 
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
	for ($i = 0; $i <= $this->numHiddenLayers; $i++) {	
            
            if ($i > 0) {
               $inputs = $outputs; //vystup predchadzajucej vrstvy je vstup do aktualnej
            }

            $outputs = [];

            // Všetky neuróny
            for ($j=0; $j < $this->layers[$i]->numNeurons; $j++) {
                
                $netinput   = 0;
                $num_imputs = $this->layers[$i]->neurons[$j]->numInputs;
                
                // Všetky váhy
                for ($k = 0; $k < $num_imputs; $k++) {
                    $netinput += $this->layers[$i]->neurons[$j]->weights[$k] * $inputs[$k];
                }
                $netinput += $this->layers[$i]->neurons[$j]->weights[$num_imputs] * Params::$bias;

                // aktivačná funkcia
                array_push($outputs, $this->transfer($i, $netinput));
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
        
        $log = fopen(__DIR__ . '/mse.log', 'w');
        $iol = fopen(__DIR__ . '/io.log', 'w');
        $wgh = fopen(__DIR__ . '/weights.php', 'w');

        $this->preProcessing($trainData); //normalize

        while($epoch < Params::$numEpochs){
            $cmse = 0;
            
            foreach ($trainData as $index => $sample) {
                $inputs  = $sample['inputs'];
                $desired = $sample['outputs'];
                
                $outputs = $this->compute($inputs); 
                $deltas  = $this->computeDeltas($outputs, $desired);
                $this->updateWeights($deltas, Params::$learnRate);
                
                $cmse += $mse = $this->meanSquaredError($outputs, $desired);

                if($epoch % 50 == 0){
                    fwrite($iol, 
                        $index . ' - ' . $mse 
                        . ' Desired: ' . ($this->mean[0] + ($desired[0] * $this->deviation[0]))
                        . ' Outputs: ' . ($this->mean[0] + ($outputs[0] * $this->deviation[0])) . PHP_EOL
                    );
                }
            }
            
            fwrite($log, $cmse / count($trainData) . PHP_EOL);
            
            if($cmse < Params::$mseTarget){
                break;
            }
            $epoch++;
        }
        
        $this->preProcessing($trainData); //denormalize

        fwrite($wgh, 
            '<?php ' . PHP_EOL
            . '$weights = ' .  var_export($this->getWeights(), TRUE) . '; ' . PHP_EOL 
            . '$mean = ' . var_export($this->mean, TRUE) . PHP_EOL 
            . '$deviation = ' . var_export($this->mean, TRUE) . PHP_EOL 
        );
    }
    
    /**
     * Inicializuje sieť na natrénované váhy
     */
    public function wakeup(){
        
        require __DIR__ . '/weights.php';
        
        $this->mean = $mean;
        $this->deviation = $deviation;
        $this->putWeights($weights);
    }
    /**
     * Spustí normalizáciu vstupov a výstupov pred započatím trénovania 
     * Pre každý vstupný atribút vypočíta priemer hodnôt a smerodajnú odchýlku    
     * @param array $trainData
     */
    private function preProcessing(&$trainData){
        
        $this->deviation = $this->mean = $sum = [];
        
        foreach (['inputs', 'outputs'] as $side){
            
            foreach ($trainData as $sample){ // spočita hodnoty vstupov jednotlivo
                foreach($sample[$side] as $input => $value){
                    $sum[$input] += $value;
                }
            }

            foreach ($sum as $input => $input_sum){ // priemerna hodnota pre každý atribút
                $this->mean[$input] = $input_sum / count($trainData);
            }

            $sum = []; //reset pomocnej 

            foreach ($trainData as $sample){ // sumuje hodnoty pre varianciu
                foreach($sample[$side] as $input => $value){
                    $sum[$input] += pow($value - $this->mean[$input],2);
                }
            }

            foreach ($sum as $input => $input_sum){ // smerodajná odchýlka pre každý atribút
                $this->deviation[$input] = sqrt($input_sum / count($trainData));
            }
        
            $this->normalize($trainData, $side);
        }
    }
    
    /**
     * Spustí prevod dát na pôvodné hodnoty po skončení trénovania
     * @param type $trainData
     */
    private function postProcessing(&$trainData){
        
        $this->denormalize($trainData, 'outputs');
    }
    
    /**
     * Normalizácia dát
     * Každú hodnotu potom normalizuje, odčítaním priemeru a vydelením smerodajnou odchýlkou
     * @param array $trainData
     * @param string $side 'inputs' alebo 'outputs'
     */
    public function normalize(&$trainData, $side){
                
        foreach ($trainData as $key => $sample){ // normalizuje dáta
            foreach($sample[$side] as $input => $value){
                $trainData[$key][$side][$input] = ($value - $this->mean[$input]) / $this->deviation[$input];
            }
        }
    }
    
    /**
     * Denormalizuje dáta späť na pôvodné hodnoty
     * @param array $trainData
     * @param string $side
     */
    public function denormalize(&$trainData, $side){
        
        foreach ($trainData as $key => $sample){ 
            foreach($sample[$side] as $input => $value){
                $trainData[$key][$side][$input] = $this->mean[$input] + ($value * $this->deviation[$input]);
            }
        }
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
            $derivative     = $this->deriveTransfer($l, $outputs[$k]);
            $deltas[$l][$k] = (-1) * $derivative * ($desired[$k] - $outputs[$k]); 
        } // called "delta rule" as well
                
        for($l = count($this->layers)-2; $l >= 0; $l--){ //hidden layers

            for($i = 0; $i < $this->layers[$l]->numNeurons; $i++){
                $sum = 0.0;

                for($j = 0; $j < $this->layers[$l+1]->numNeurons; $j++){
                    $sum += $this->layers[$l+1]->neurons[$j]->weights[$i] * $deltas[$l+1][$j];
                }
                $deltas[$l][$i] = $sum * $this->deriveTransfer($l, $this->partialOutputs[$l][$i]); 
            }
        }
        
        return $deltas;
    }
    
    /**
     * Počíta mean squared error
     * 
     * @param type $outputs
     * @param type $desired
     * @return float
     */
    private function meanSquaredError($outputs, $desired) {

        $sumSquaredError = 0.0;

        for ($j = 0; $j < $this->numOutputs; ++$j) {
            $sumSquaredError += pow($desired[$j] - $outputs[$j], 2) / 2;
        }

        return $sumSquaredError;
    }
    
    /**
     * --------------------------------------------------
     * @todo Aktivačné funkcie by mali mať vrstvy, logicky
     * --------------------------------------------------
     */
    
    /**
     * Volá aktivačnú funkciu podľa konfigurácie vrstvy
     * @param int $layer
     * @param float $value
     */
    private function transfer($layer, $value){
        
        $method = Params::$activationFunction[$layer];
        
        if(method_exists($this, $method)) {
            
            return call_user_func(array($this, $method), $value);
        }
        else {
            trigger_error('Neznáma aktivačná funkcia: ' . $method, E_USER_ERROR);
        }
    }
    
    /**
     * Volá deriváciu aktivačnej funkcie podľa konfigurácie vrstvy
     * @param type $layer
     * @param type $value
     */
    private function deriveTransfer($layer, $value) {
        
        $method = 'derive' . ucfirst(Params::$activationFunction[$layer]);
        
        if(method_exists($this, $method)) {
            
            return call_user_func(array($this, $method), $value);
        }
        else {
            trigger_error('Neznáma derivácia aktivačnej funkcie: ' . $method, E_USER_ERROR);
        }
    }
    
    /**
     * Lineárna aktivačná funkcia
     * @param float $value
     * @return float
     */
    private function linear($value){
        
        return $value;
    }
    
    /**
     * Derivácia lineárnej aktivačnej funkcie
     * @param float $value
     * @return int
     */
    private function deriveLinear($value){
        
        return 1;
    }
    
    /**
     * Sigmoid aktivačná funkcia 
     * @param float $value
     * @return float
     */
    private function sigmoid($value){
        
        return ( 1 / ( 1 + exp(-$value / Params::$sigmoidActivationResponse)));
    }
    
    /**
     * Derivácia sigmoid funkcie
     * @return float
     */
    private function deriveSigmoid($value){
        
        return (1 - $value) * $value;
    }
    
    /**
     * Hyperbolický tangens aktivačná funkcia
     * @param float $value
     * @return float
     */
    private function tanh($value){
        
        return tanh($value);
    }
    
    /**
     * Derivácia hyperbolického tangensu
     * @param type $value
     * @return float
     */
    private function deriveTanh($value){

        return 1 / pow(cosh($value),2);
    }
}
