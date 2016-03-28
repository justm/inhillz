<?php

namespace inhillz\components;

use inhillz\models\ActivityModel;
use inhillz\models\GearModel;
use inhillz\models\UserModel;
use inhillz\models\WorkoutModel;
use orchidphp\Orchid;

/**
 * Trieda Analyzer vykonáva výpočet odhadovaného výkonu.
 * Zároveň získava koeficienty potrebné pre výpočet odhadovaného výkonu z existujúcich tréningov
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
     * Pole udajov pre vypocet koeficientov
     * @var array
     */
    private $learn_data = [];
    
    /**
     * Metóda, ktorá spúšťa analýzu tréningu
     * Validuje dostupnosť súboru s dátami, a typ aktivity
     * @param WorkoutModel $workout
     */
    public function analyze( WorkoutModel $workout ){
        
        $this->data_record = ActivityModel::get_record($workout->id, $workout->data_file);
        $this->workout = $workout;
        
        if ($workout->id_activity == 1){
                            
            $this->athlete = UserModel::model()->findById($this->workout->id_user);
            $this->bike = GearModel::model()->findById($this->workout->id_gear);

            if( empty($this->athlete) || empty($this->bike) ){
                return $this->data_record;
            }
            
            $this->detect_climbs();
            
            /* TEMP <- SHOULD BE MOVED TO UPLOAD CONTROLLER */
            if( !empty(array_column($this->data_record, 'power')) ){
                foreach ($this->segments as $seg){
                    $this->estimate_coef($seg);
                    $this->estimate_power($seg);
                }
                $this->learn();
            } /* TEMP ENDS HERE */
            elseif( empty(array_column($this->data_record, 'est_power')) ){
               foreach ($this->segments as $seg){
                    $this->estimate_power($seg);
                }
            }
                        
            ActivityModel::save_record($this->data_record, $workout->id);
        }
        
        return $this->data_record;
    }
    
    /**
     * Dokončenie odhadu CdA a Crr
     */
    public function learn() {
        $avg_cda =  array_sum( array_column($this->learn_data, 'cda_coef') ) / count( array_column($this->learn_data, 'cda_coef') );
        Orchid::var_dump( $avg_cda );
        
        $calc_pw = []; // Hodnoty výkonu vypočítané podľa odhadnutého koeficientu
                
        do {
            foreach ( $this->learn_data as $s ){
                $calc_pw[] = $this->calculate_power($s['segment'], $s['crr_coef'], $avg_cda, $this->bike->weight + $this->athlete->weight);
                
                //Orchid::var_dump( [array_sum($calc_pw)/count($calc_pw), $s['P']] );
            }
        } while( 0 /*Nejaka presnost*/ );   
        
        $gear = GearModel::model()->findById($this->workout->id_gear);
        $gear->cda_coef;
        $gear->crr_coef;
        $gear->save();
    }
    
    /**
     * Detekuje stúpania pre analýzu výkonu
     * Úsek je považovaný za stúpanie pri splnení nasledujúcich podmienok
     * - dlhsie ako 500m, 
     * - priemerné stúpanie majú viac ako 3% 
     * - násobok dĺžka*gradient dosahuje viac ako 40bodov
     */
    public function detect_climbs( ){
        
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
                $this->detect_segments($climb);
            }
        }
    }
    
    /**
     * Rozdelí stúpanie na čiastkové úseky s konštatným stúpaním
     * @param SegmentModel $climb
     */
    private function detect_segments( SegmentModel $climb ){
        
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
     * Inicializacia testovacich dat, ziskanie koeficientu CdA podľa zmeraneho vykonu
     * @param SegmentModel $segment
     * @param floar $crr_coef
     */
    private function estimate_coef( SegmentModel $segment ) {
        
        if( $segment->grade() < 0.04 && $segment->grade() > 0.07 ){
            return;
        }
        
        $athlete = $this->athlete;
        $bike    = $this->bike;
        $crr     = empty($this->bike->crr_coef)? 0.003 : $this->bike->crr_coef;
        
        $data = array_slice($this->data_record, $segment->get_index_start(), $segment->get_index_end()-$segment->get_index_start());
        $P = array_sum(array_column($data, 'power')) / count($data);
        
        $Fg = ($athlete->weight + $bike->weight) * $this->constants['g'] * $segment->grade(); // Gravity
        $Fr = ($athlete->weight + $bike->weight) * $this->constants['g'] * cos(asin($segment->grade())) * $crr; // Rolling resistance
        $p1 = $this->constants['p0'] * pow(
                1-($this->constants['L'] * $this->data_record[$segment->get_index_start()]['altitude'] / $this->constants['T0']), 
                $this->constants['gm_lr']
            ); // Air pressure
        $ro = $p1 * $this->constants['M'] / ($this->constants['R'] * ($this->data_record[$segment->get_index_start()]['temperature']+273.15) ); // Air density
        $v  = $segment->get_length() / ($this->data_record[$segment->get_index_end()]['timestamp'] - $this->data_record[$segment->get_index_start()]['timestamp']);
        
        $Fd = ( $P/$v ) - ( $Fg+$Fr );
                
        $this->learn_data[] = [
            'cda_coef' => $Fd / (0.5 * $ro * pow($v, 2)),
            'crr_coef' => $crr,
            'P' => $P,
            'v' => $v,
            'segment' => $segment,
        ];
    }
    
    /**
     * Odhadne wattový výkon pre vstupný úsek
     * @param SegmentModel $segment
     */
    private function estimate_power( SegmentModel $segment ){
                                       
        $P = $this->calculate_power(
                $segment, 
                $this->bike->crr_coef, 
                $this->bike->cda_coef, 
                $this->athlete->weight + $this->bike->weight
            );
        
        for($i = $segment->get_index_start(); $i < $segment->get_index_end(); $i++){
            $this->data_record[$i]['est_power'] = round($P);
        }
    }
    
    /**
     * Fyzikálny výpočet výkonu
     * @param SegmentModel $segment
     * @param float $crr_coef
     * @param float $cda_coef
     * @param float $weight
     * @return float
     */
    private function calculate_power( SegmentModel $segment, $crr_coef, $cda_coef, $weight ){
                 
        $Fg = $weight * $this->constants['g'] * $segment->grade(); // Gravity
        $Fr = $weight * $this->constants['g'] * cos(asin($segment->grade())) * $crr_coef; // Rolling resistance
        
        $p1 = $this->constants['p0'] * pow(
                1-($this->constants['L'] * $this->data_record[$segment->get_index_start()]['altitude'] / $this->constants['T0']), 
                $this->constants['gm_lr']
            ); // Air pressure
        $ro = $p1 * $this->constants['M'] / ($this->constants['R'] * ($this->data_record[$segment->get_index_start()]['temperature']+273.15) ); // Air density
        $v  = $segment->get_length() / ($this->data_record[$segment->get_index_end()]['timestamp'] - $this->data_record[$segment->get_index_start()]['timestamp']);
        
        $Fd = 0.5 * $ro * $cda_coef * pow($v, 2); //Drag resistance
        
        return ($Fg+$Fd+$Fr) * $v;
    }
}

/*
///*** Zistenie presnosti algoritmu
    $odch = [];
    for ( $j = $climb->get_index_start()+2; $j < $climb->get_index_end(); $j++){
        $odch[] = abs(
                    ($this->data_record[$j]['power'] + $this->data_record[$j-1]['power'] + $this->data_record[$j-2]['power'])/3
                    - $this->data_record[$j]['est_power']
                );
        $pow[] = $this->data_record[$j]['power'];
        $epow[] = $this->data_record[$j]['est_power'];
    }
    unset( $odch[array_search(max($odch), $odch)] );
    unset( $odch[array_search(min($odch), $odch)] );

    $p_avg = array_sum($pow) / count($pow);
    $ep_avg = array_sum($epow) / count($epow);
    $o_avg = array_sum($odch) / count($odch);

    echo 
        '<tr>',
            '<td>' . $climb->get_length() . '</td>',
            '<td>' . round($p_avg) . '</td>',
            '<td>' . round($ep_avg) . '</td>',
            '<td>' . number_format( abs($p_avg - $ep_avg ),2 ) . ' (' . number_format( 100 * abs( $p_avg - $ep_avg )/$p_avg, 1) . '%)</td>',
            '<td>' . number_format($o_avg,2) . ' (' . ( number_format( 100 * $o_avg/$p_avg, 1) ) . '%)</td>',
        '</tr>';
 */