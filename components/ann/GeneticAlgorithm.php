<?php

namespace inhillz\components\ann;

use inhillz\components\Helper;

/**
 * GeneticAlgorithm - Genetický algoritmus
 * 
 * @namespace  inhillz\components\ann; 
 * @author     Matus Macak <matus.macak@orchidsphere.com>
 * @link       http://ride.inhillz.com/
 * @version    2.0
 */
class GeneticAlgorithm {

    /**
     * @var array of Genome Celá populácia chromozómov
     */
    private $m_vecPop = [];

    /**
     * @var int Veľkosť populácie
     */
    private $m_iPopSize = 0;
	
    /**
     * @var int Amount of weights per chromo
     */
    private $m_iChromoLength = 0;

    /**
     * @var float Total fitness of population
     */
    private $m_dTotalFitness = 0.0;

    /**
     * @var float Best fitness this population
     */
    private $m_dBestFitness = 0.0;

    /**
     * @var float Average fitness
     */
    private $m_dAverageFitness = 0.0;

    /**
     * @var float worst
     */
    private $m_dWorstFitness = INF;

    /**
     * @var int Keeps track of the best genome
     */
    private $m_iFittestGenome = 0;

    /**
     * @var float Pravdepodobnosť mutácie chromozómov, medzi 0.05 až 0.3
     */
    private $m_dMutationRate = 0.05;

    /**
     * @var float Pravdepodobnosť kríženia
     */
    private $m_dCrossoverRate = 0.7;

    /**
     * @var int Počítadlo generácií
     */
    private $m_cGeneration = 0;
    
    /**
     * Konštruktor
     * @param type $popSize
     * @param type $mutRate
     * @param type $crossRate
     * @param type $numWeights
     */
    public function __construct($popSize, $mutRate, $crossRate, $numWeights) {

        $this->m_iPopSize       = $popSize;
        $this->m_dMutationRate  = $mutRate;
        $this->m_dCrossoverRate = $crossRate;
        $this->m_iChromoLength  = $numWeights;

        // Inicializuje sa populácia s chromozómami s náhodnými váhami a fitness = 0
	for ($i = 0; $i < $this->m_iPopSize; $i++) {
            $weights = [];
            
            for ($j=0; $j < $this->m_iChromoLength; $j++){
                
                $weights[] = Helper::random(-1, 1);
            }
            
            array_push($this->m_vecPop, new Genome($weights, 0));
	}
    }
    
    /**
     * 
     * @param array $mum
     * @param array $dad
     * @param array $baby1
     * @param array $baby2
     * @return void
     */
    private function Crossover($mum, $dad, $baby1, $baby2){
        
        //just return parents as offspring dependent on the rate
	//or if parents are the same
	if ((Helper::random(0, 1) > $this->m_dCrossoverRate) || (empty(array_diff($mum, $dad)) && empty(array_diff($dad, $mum))) ) {
            
            $baby1 = $mum;
            $baby2 = $dad;

            return;
	}

	//determine a crossover point
        $cp = Helper::random(0, $this->m_iChromoLength - 1, 0);

	//create the offspring
	for ($i=0; $i < $cp ; $i++) {
            
            array_push($baby1, $mum[$i]);
            array_push($baby2, $dad[$i]);
	}
	for ($i=$cp; $i < count($mum); $i++) {
            
            array_push($baby1, $dad[$i]);
            array_push($baby2, $mum[$i]);
	}	
	
	return;
    }
    
    /**
     * Prejde všetky vstupné chromozómy a mutuje s pravdepodobnosťou $this->m_dMutationRate
     * @param array $chromo
     * @return void
     */
    private function Mutate($chromo){
        
	for ($i = 0; $i < count($chromo); $i++) {
            
            if (Helper::random(0,1) < $this->m_dMutationRate){
                $chromo[$i] += (Helper::random(-1,1) * Params::dMaxPerturbation);
            }
	}
    }
    
    /**
     * @return Genome
     */
    private function GetChromoRoulette(){
        
	$threshold = Helper::random(0, $this->m_dTotalFitness);
	$fitness   = 0;
	$chosenOne = NULL;
	
	for ($i = 0; $i < $this->m_iPopSize; $i) {
            
            $fitness += $this->m_vecPop[$i]->dFitness;

            if ($fitness >= $threshold) {
                $chosenOne = $this->m_vecPop[$i];
                break;
            }
	}

	return $chosenOne;
    }

    /**
     * Add the required amount of copies of the n most fittest 
     * @param int $Nbest
     * @param int $numCopies
     * @param array of Genome $population
     * @return void
     */
    private function GrabNBest($Nbest, $numCopies, &$population){
        
	while($Nbest--) {
            for ($i = 0; $i < $numCopies; $i++){
                array_push($population, $this->m_vecPop[($this->m_iPopSize - 1) - $Nbest]);
            }
	}
    }
    
    /**
     * @return void
     */
    private function CalculateBestWorstAvTot(){
        $this->m_dTotalFitness = 0;
	
	$highest = 0;
	$lowest  = INF;
	
	for ($i=0; $i< $this->m_iPopSize; $i++) {
            
            if ($this->m_vecPop[$i]->dFitness > $highest) {
                
                $highest                 = $this->m_vecPop[$i]->dFitness;
                $this->m_iFittestGenome  = $i;
                $this->m_dBestFitness	 = $highest;
            }

            if ($this->m_vecPop[$i]->dFitness < $lowest) {
                
                $lowest                = $this->m_vecPop[$i]->dFitness;
                $this->m_dWorstFitness = $lowest;
            }
            $this->m_dTotalFitness += $this->m_vecPop[$i]->dFitness;
	}
	$this->m_dAverageFitness = $this->m_dTotalFitness / $this->m_iPopSize;
    }
    
    /**
     * @return void
     */
    private function Reset(){
        
        $this->m_dTotalFitness   = 0;
	$this->m_dBestFitness	 = 0;
	$this->m_dWorstFitness	 = INF;
	$this->m_dAverageFitness = 0;
    }

    /**
     * 
     * @param array of Genome $old_pop
     * @return array of Genome
     */
    public function Epoch($old_pop){
        
        $this->m_vecPop = $old_pop;

        $this->reset();

        usort($this->m_vecPop, function($a, $b) {return $a->dFitness < $b->dFitness;}); //sort the population

        $this->CalculateBestWorstAvTot();
        
	$vecNewPop = [];

	if (!(Params::iNumCopiesElite * Params::iNumElite % 2)) {
            
            $this->GrabNBest(Params::iNumElite, Params::iNumCopiesElite, $vecNewPop);
	}
	
	//repeat until a new population is generated
	while (count($vecNewPop) < $this->m_iPopSize) {
            
		$mum = $this->GetChromoRoulette();
		$dad = $this->GetChromoRoulette();

		$baby1 = $baby2 = [];

		$this->Crossover($mum->vecWeights, $dad->vecWeights, $baby1, $baby2);

		$this->Mutate($baby1);
		$this->Mutate($baby2);

		//now copy into $vecNewPop population
		array_push($vecNewPop, Genome($baby1, 0));
		array_push($vecNewPop, Genome($baby2, 0));
	}

	$this->m_vecPop = $vecNewPop;

	return $this->m_vecPop;
    }
    
    /**
     * @return array of Genome
     */
    public function GetChromos(){
        return $this->m_vecPop;
    }
    
    /**
     * @return float
     */
    public function AverageFitness(){
        return $this->m_dTotalFitness / $this-> m_iPopSize;
    }

    /**
     * @return float
     */
    public function  BestFitness() {
        return $this->m_dBestFitness;
    }
}
