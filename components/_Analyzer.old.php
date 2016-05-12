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
     * Dokončenie odhadu CdA a Crr
     */
    public function learn() {
        
        $avg_cda =  array_sum( array_column($this->learn_data, 'cda_coef') ) / count( array_column($this->learn_data, 'cda_coef') );
        $calc_pw = []; // Hodnoty výkonu vypočítané podľa odhadnutého koeficientu
                
        do {
            foreach ( $this->learn_data as $s ){
                $calc_pw[] = $this->calculate_power($s['segment'], $s['crr_coef'], $avg_cda, $this->bike->weight + $this->athlete->weight);
            }
        } while( 0 /*Nejaka presnost*/ );   
        
        $gear = GearModel::model()->findById($this->workout->id_gear);
        $gear->cda_coef;
        $gear->crr_coef;
        $gear->save();
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