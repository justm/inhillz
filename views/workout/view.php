<?php

use inhillz\models\WorkoutModel;

/**
 * Náhľad pre detailné zobrazenie záznamu o tréningu
 *
 * @package    inhillz\views
 * @author     Matus Macak <matus.macak@orchidsphere.com>
 * @link       http://ride.inhillz.com/
 * @version    2.0
 * 
 * @var WorkoutModel $data->workout_summary
 * @var Array $data->workout_data
 */
    //** Priprava jednotlivych bodov pre vykreslenie na mape a grafe
    $deg_to_semic = 180 / pow(2, 31);
    $coordinates  = '';
    $bounds       = '';
    $chart_data    = '';
    
    //** Rozhodne, ktorú veličinu je možné zobraziť v grafe, tj. ku ktorej sú data
    $chart_def  = !empty( array_column($data->workout_data, 'cadence') )?   'cadence:{name:"Cadence",precision:0,unit:"rpm"},' : '';   
    $chart_def .= !empty( array_column($data->workout_data, 'heart_rate') )?'heart_rate:{name:"Heart Rate",precision:0,unit:"bpm"},' : '';   
    $chart_def .= !empty( array_column($data->workout_data, 'speed') )?     'speed:{name:"Speed",precision:2,unit:"kph"},' : '';   
    $chart_def .= !empty( array_column($data->workout_data, 'est_power') )? 'est_power:{name:"Est. Power",precision:0,unit:"W"},' : '';   
    $chart_def .= !empty( array_column($data->workout_data, 'power') )?     'power:{name:"Power",precision:0,unit:"W"},' : '';   
    $chart_def .= !empty( array_column($data->workout_data, 'altitude') )?  'altitude:{name:"Altitude",precision:0,unit:"m"},' : '';   
        
    //** Pripraví data
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
            $chart_data .= "{i:{$i},distance:" . ($r['distance']/1000) . ",altitude:" . round($r['altitude']) . ", {$latlng}";
        }
        else{
            continue;
        }
        $chart_data .= empty($r['speed'])? '' : "speed:" . round( $r['speed']*3.6, 2 ) . ",";
        $chart_data .= empty($r['cadence'])? '' : "cadence:{$r['cadence']},";
        $chart_data .= empty($r['heart_rate'])? '' : "heart_rate:{$r['heart_rate']},";
        $chart_data .= empty($r['power'])? '' : "power:{$r['power']},";
        $chart_data .= empty($r['est_power'])? 'est_power:0,' : "est_power:{$r['est_power']},";
        $chart_data .= "},";
    }
        
    include 'view.map.php';
    include 'view.basics.php';
    include 'view.chart.php';