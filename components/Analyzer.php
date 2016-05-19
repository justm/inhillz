<?php

namespace inhillz\components;

use inhillz\components\ann\NeuralNet;
use inhillz\models\ActivityModel;
use inhillz\models\GearModel;
use inhillz\models\SegmentModel;
use inhillz\models\UserModel;
use inhillz\models\WorkoutModel;

/**
 * Trieda Analyzer vykonáva výpočet odhadovaného výkonu.
 *
 * @package    inhillz\components
 * @author     Matus Macak <matus.macak@orchidsphere.com>
 * @link       http://ride.inhillz.com/
 * @version    2.0
 */
class Analyzer {
    
    /**
     * Pole fyzikálnych konštánt používaných pri výpočte výkonu
     * @var array 
     */
    private $constants = [
        'p0' => 101325,
        'T0' => 288.15,
        'g'  => 9.80665,
        'L'  => 0.0065,
        'R'  => 8.31447,
        'M'  => 0.0289644,
        'gm_rl' => 5.25578129,
    ];
    
    /**
     * Načítané hodnoty z tréningu
     * @var array
     */
    private $data_record = [];
    
    /**
     * Sumár tréningu
     * @var WorkoutModel
     */
    private $workout;
    
    /**
     * Športovec
     * @var UserModel
     */
    private $athlete;
    
    /**
     * Bicykel
     * @var GearModel
     */
    private $bike;
           
    /**
     * Detekovane segmenty
     * @var array
     */
    private $segments = [];
        
    /**
     * Metóda, ktorá spúšťa analýzu tréningu
     * Validuje dostupnosť súboru s dátami, a typ aktivity
     * @param WorkoutModel $workout
     */
    public function analyze(WorkoutModel $workout){
        
        $this->data_record = ActivityModel::getRecord($workout->id, $workout->data_file);
        $this->workout = $workout;
        
        if ($workout->id_activity == 1){
                            
            $this->athlete = UserModel::model()->findById($this->workout->id_user);
            $this->bike    = GearModel::model()->findById($this->workout->id_gear);

            if( empty($this->athlete) || empty($this->bike) ){
                return $this->data_record;
            }
            
            if(empty(array_column($this->data_record, 'power')) && empty(array_column($this->data_record, 'est_power'))){
                $this->detectClimbs();

                foreach ($this->segments as $seg){
                    $this->estimatePower($seg);
                }
                ActivityModel::saveRecord($this->data_record, $workout->id);
            }
        }
        
        return $this->data_record;
    }
   
    /**
     * Inicializuje Analyzer
     * načíta údaje z tréningu pre ďalšie operácie, napr. detectClimbs()
     * @param WorkoutModel $workout
     * @return array
     */
    public function load(WorkoutModel $workout){
        
        $this->workout = $workout;
        $this->athlete = UserModel::model()->findById($this->workout->id_user); 
        $this->bike    = GearModel::model()->findById($this->workout->id_gear);
        
        return $this->data_record = ActivityModel::getRecord($workout->id, $workout->data_file);
    }

    /**
     * Odhadne výkon pre vstupný segment
     * @param SegmentModel $segment
     */
    public function estimatePower(SegmentModel $segment){
        
        $net = new NeuralNet();
        //$net->putWeights(); //vopred trénované váhy
        
        $P = $net->compute([
            
        ]);
        
        for($i = $segment->get_index_start(); $i < $segment->get_index_end(); $i++){
            $this->data_record[$i]['est_power'] = round($P);
        }
    }
    
    /**
     * Detekuje stúpania pre analýzu výkonu
     * Úsek je považovaný za stúpanie pri splnení nasledujúcich podmienok
     * - dlhsie ako 500m, 
     * - priemerné stúpanie majú viac ako 3% 
     * - násobok dĺžka*gradient dosahuje viac ako 40bodov
     */
    public function detectClimbs(){
        
        $climbs = [];
        
        for( $i = 0; $i < count($this->data_record); $i++ ){
            if( empty($this->data_record[$i]['distance']) ){
                continue;
            }
            
            $start = [ 
                'index' => $i,
                'distance' => $this->data_record[$i]['distance'], 
                'altitude' => $this->data_record[$i]['altitude']
            ];
            while( $this->data_record[$i]['altitude'] <= $this->data_record[$i+1]['altitude'] ){ 
                $i++; 
            }
            $end = [
                'index' => $i,
                'distance' => $this->data_record[$i]['distance'], 
                'altitude' => $this->data_record[$i]['altitude']
            ];
            
            $climbs[] = new SegmentModel(
                            $start['index'],
                            $end['index'],
                            $end['distance'] - $start['distance'],
                            abs($end['altitude'] - $start['altitude'])
                        );
        }
        
        //** Odstrani nevyhovujuce stúpania
        foreach ( $climbs as $k => $climb ){
            
            if( !($climb->get_length() >= 500 && $climb->grade() >= 0.03 && $climb->get_length() * $climb->grade() > 40) ){
                unset($climbs[$k]);
            }
            else{
                $this->detectSegments($climb);
            }
        }
    }
    
    /**
     * Rozdelí stúpanie na čiastkové úseky s konštatným stúpaním
     * @param SegmentModel $climb
     */
    private function detectSegments(SegmentModel $climb){
        
        //** Dáta sú prechádzané po skokoch $step, aby sa minimalizoval vplyv výkyvov a priebeh stúpania sa "vyhladil"
        $step = 10;
        
        $seg_start = $i = $climb->get_index_start();
        
        $length = $this->data_record[$i+$step]['distance'] - $this->data_record[$i]['distance'];
        $elevat = $this->data_record[$i+$step]['altitude'] - $this->data_record[$i]['altitude'];
        $grade_prev  = $elevat / $length;
        
        for( $i = $i+1; $i <= $climb->get_index_end() - $step; $i++ ){
            
            //** Spočíta gradient
            $length = $this->data_record[$i+$step]['distance'] - $this->data_record[$i]['distance'];
            $elevat = abs($this->data_record[$i+$step]['altitude'] - $this->data_record[$i]['altitude']); 
            $grade  = $elevat / $length;
            
            //** Check data consistency
            if($length <= 0){ 
                continue;
            }
            
            if( abs($grade - $grade_prev) > 0.005 && $i-$seg_start > $step){
                   
                //** Detekcia bodu nového segmentu
                /**  Môže nastať situácia kedy koncový bod by mal patriť do nového segmentu ale začiatočný ešte do aktuálneho, preto overenie s krokom 1 */
                $l1 = $this->data_record[$i+$step/2]['distance'] - $this->data_record[$i]['distance'];
                $l2 = $this->data_record[$i+$step/2]['distance'] - $this->data_record[$i]['distance'];
                $e1 = abs($this->data_record[$i+$step]['altitude'] - $this->data_record[$i+$step/2]['altitude']);
                $e2 = abs($this->data_record[$i+$step]['altitude'] - $this->data_record[$i+$step/2]['altitude']);
                
                if( abs($e1/$l1 - $e2/$l2) > 0.005 ){
                    $i--;
                }
                $t_start   = $seg_start;
                $seg_start = $seg_end = $i+$step;

                $this->segments[] = new SegmentModel(
                                        $t_start,
                                        $seg_end,
                                        $this->data_record[$seg_end]['distance'] - $this->data_record[$t_start]['distance'],
                                        abs($this->data_record[$seg_end]['altitude'] - $this->data_record[$t_start]['altitude'])
                                    );
            }
            
            $grade_prev = $grade;
        }
    }    
    
    /**
     * Vypočíta priemerný výkon pre vstupný segment
     * @param SegmentModel $segment
     * @param bool $computeStrict Ak je TRUE a v dátach je nulová hodnota výkonu, metóda vráti FALSE. 
     * Použitie pri operáciach kedy sú prázdne/nulové hodnoty výkonu nežiadúce
     * @return float|bool
     */
    public function averagePower(SegmentModel $segment, $computeStrict = FALSE){
        
        $sum = $cnt = 0;
        for($i = $segment->get_index_start(); $i < $segment->get_index_end(); $i++){
            
            if(!empty($this->data_record[$i]['power'])){
                $sum += $this->data_record[$i]['power'];
                $cnt++;
            }
            elseif($computeStrict){
                return FALSE;
            }
        }
        
        if($sum == 0){
            return 0;
        }
        return $sum / $cnt;
    }
    
    /**
     * Dáta segmentu pre vyhodnotenie neurónovou sieťou
     * @return array
     */
    public function segmentData(SegmentModel $segment){
        
        return [
            'grade'          => (float) $segment->grade(),
            'altitude'       => (float) $this->data_record[$segment->get_index_start()]['altitude'],
            'temperature'    => (float) $this->data_record[$segment->get_index_start()]['temperature'],
            'speed'          => (float) ($segment->get_length() / ($this->data_record[$segment->get_index_end()]['timestamp'] - $this->data_record[$segment->get_index_start()]['timestamp'])),
            'bike_weight'    => (float) $this->bike->weight,
            'athlete_weight' => (float) $this->athlete->weight,
        ];
    }

    /**
     * Getter segmentov
     * @return array
     */
    public function getSegments() {
        return $this->segments;
    }
}