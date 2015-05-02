<?php
/**
 * Náhľad pre detailné zobrazenie záznamu o tréningu
 *
 * @author Matus Macak < matus.macak@folcon.sk > 
 * @version 2.0
 * @since Subor je súčasťou aplikácie od verzie 2.0
 * @package views.workout
 * 
 * @var WorkoutModel $data->workout_summary
 * @var Array $data->workout_data
 */
    //** Priprava jednotlivych bodov pre vykreslenie na mape a grafe
    $deg_to_semic = 180 / pow(2, 31);
    $coordinates  = '';
    $bounds       = '';
    $chart_data    = '';
    
    /** @todo Rozhodni, ktorú veličinu je možné zobraziť v grafe, tj. ku ktorej sú data*/
        
    for ($i = 0; $i< count($data->workout_data); $i+=4){
        $r = $data->workout_data[$i];
        
        //** map
        if( !empty($r['position_lat']) && !empty($r['position_long']) ) {
            $latlng       = 'position_lat:' . ($r['position_lat'] * $deg_to_semic) . ', position_long:' . ($r['position_long'] * $deg_to_semic) . ',';
            $latlngMaps   = 'new google.maps.LatLng(' . ($r['position_lat'] * $deg_to_semic) . ',' . ($r['position_long'] * $deg_to_semic) . ')';
            $bounds      .= 'map_bns.extend(' . $latlngMaps . '); ';
            $coordinates .= $latlngMaps . ',';
        }
        else{
            continue;
        }
        
        //** chart
        if( !empty($r['distance']) && !empty($r['altitude']) ) {
            $chart_data .= "{i:{$i},distance:" . ($r['distance']/1000) . ",altitude:" . number_format( $r['altitude'], 0 ) . ", {$latlng}";
        }
        else{
            continue;
        }
        $chart_data .= empty($r['speed'])? '' : "speed:" . number_format( $r['speed']*3.6, 2 ) . ",";
        $chart_data .= empty($r['cadence'])? '' : "cadence:{$r['cadence']},";
        $chart_data .= empty($r['heart_rate'])? '' : "heart_rate:{$r['heart_rate']},";
        $chart_data .= "},";
    }
    
    include 'view.map.php';
    include 'view.basics.php';
    include 'view.chart.php';