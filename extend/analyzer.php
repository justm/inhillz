<?php
/**
 * Súbor obsahuje triedu Analyzer
 *
 * @author Matus Macak < matus.macak@folcon.sk > 
 * @version 2.0
 * @since Subor je súčasťou aplikácie od verzie 2.0
 * @package controllers
 * 
 */

/**
 * Trieda Analyzer vykonáva výpočet odhadovaného výkonu.
 * Zároveň získava koeficienty potrebné pre výpočet odhadovaného výkonu z existujúcich tréningov
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
     * Metóda, ktorá spúšťa analýzu tréningu
     * Validuje dostupnosť súboru s dátami, a typ aktivity
     * @param WorkoutModel $workout
     */
    public function analyze( WorkoutModel $workout ){
        
        /** @todo Check activity type */
        /** @todo Check if $workout->data_file not empty, e.g. manual entry */
        /** @todo Run detect_climbs */
    }
    
    /**
     * Detekuje stúpania pre analýzu výkonu
     * Úsek je považovaný za stúpanie pri splnení nasledujúcich podmienok
     * - dlhsie ako 500m, 
     * - priemerné stúpanie majú viac ako 3% 
     * - násobok dĺžka*gradient dosahuje viac ako 40bodov
     * @param WorkoutModel $workout
     */
    public function detect_climbs( WorkoutModel $workout ){
        
        $this->data_record = ActivityModel::get_record($workout->data_file);
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
                            $end['altitude'] - $start['altitude']
                        );
        }
        
        //** Odstrani nevyhovujuce stúpania
        foreach ( $climbs as $k => $climb ){
            
            if( !($climb->get_length() > 500 && $climb->grade() >= 0.03 && $climb->get_length() * $climb->grade() > 40) ){
                unset($climbs[$k]);
            }
            else{
                $this->detect_segments($climb);
            }
        }
        
        return $this->data_record;
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
            $elevat = $this->data_record[$i+$step]['altitude'] - $this->data_record[$i]['altitude'];
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
                $e1 = $this->data_record[$i+$step]['altitude'] - $this->data_record[$i+$step/2]['altitude'];
                $e2 = $this->data_record[$i+$step]['altitude'] - $this->data_record[$i+$step/2]['altitude'];
                
                if( abs($e1/$l1 - $e2/$l2) > 0.005 ){
                    $i--;
                }
                $t_start   = $seg_start;
                $seg_start = $seg_end = $i+$step;

                $seg = new SegmentModel(
                            $t_start,
                            $seg_end,
                            $this->data_record[$seg_end]['distance'] - $this->data_record[$t_start]['distance'],
                            $this->data_record[$seg_end]['altitude'] - $this->data_record[$t_start]['altitude']
                        );
                $this->estimate_power($seg);
            }
            $grade_prev = $grade;
        }
    }
    
    /**
     * Odhadne wattový výkon pre vstupný úsek
     * @param SegmentModel $segment
     */
    public function estimate_power( SegmentModel $segment ){
                
        /* TEMP load from DB */
        $athlete = new stdClass();
        $bike = new stdClass();
        $athlete->weight = 70;
        $bike->weight = 7;
        $bike->cda_coef = 0.48;
        $bike->crr_coef = 0.00313;
                
        $Fg = ($athlete->weight + $bike->weight) * $this->constants['g'] * $segment->grade(); // Gravity
        $Fr = ($athlete->weight + $bike->weight) * $this->constants['g'] * cos(asin($segment->grade())) * $bike->crr_coef; // Rolling resistance
        
        $p1 = $this->constants['p0'] * pow(
                1-($this->constants['L'] * $this->data_record[$segment->get_index_start()]['altitude'] / $this->constants['T0']), 
                $this->constants['gm_lr']
            ); // Air pressure
        $ro = $p1 * $this->constants['M'] / ($this->constants['R'] * ($this->data_record[$segment->get_index_start()]['temperature']+273.15) ); // Air density
        $v  = $segment->get_length() / ($this->data_record[$segment->get_index_end()]['timestamp'] - $this->data_record[$segment->get_index_start()]['timestamp']);
        
        $Fd = 0.5 * $ro * $bike->cda_coef * pow($v, 2);
        
        $P  = ($Fg+$Fd+$Fr) * $v;
       
        
        for($i = $segment->get_index_start(); $i < $segment->get_index_end(); $i++){
            $this->data_record[$i]['est_power'] = round($P);
        }
    }
}