<?php

namespace inhillz\components;

use DateTime;

/**
 * Gpx_activity_parser
 * 
 * @package    inhillz\components
 * @author     Matus Macak <matus.macak@orchidsphere.com>
 * @link       http://ride.inhillz.com/
 * @version    2.0
 */
class GpxActivityParser{

    /**
     * @var String $file_name Cesta k súboru
     */
    protected $file_name = NULL;
    
    /**
     * @var resource $f Deskriptor súboru 
     */
    protected $f = FALSE;
        
    public function __construct($file_name) {
        
        $this->file_name = $file_name;
    }
    
    /**
     * Spustí čítanie súboru
     * @param ActivityParser $handler
     */
    public function parse(ActivityParser $handler){
        
        $this->openFile()->readFile($handler);
    }
    
    /**
     * Otvorí súbor
     */
    protected function openFile() {
        
        $this->closeFile();
        
        $this->f = @fopen( $this->file_name, "r" );

        return $this;
    }
    
    /**
     * 
     * @param ActivityParser $handler
     * @return GpxActivityParser
     * 
     * @todo chybaju rychlosti pre jednotlive body
     *       chybaju extensions udaje atemp, cad, hr (ich nacitanie pravdepodobne blokuje simplexml, ktore nepodporuje NS?)
     */
    protected function readFile($handler){
        
        if ( $this->f === FALSE ) {
            $handler->error = 'Data file cannot be processed, due to an internal error.';
            return;
        }
        
        $xml = simplexml_load_file($this->file_name);
        
        $data_index = 0;
        $point_curr = [];
        $point_prev = [];
        
        $start_time = new DateTime($xml->metadata->time);
        $handler->session['start_time']   = $start_time->getTimestamp();
        $handler->session['total_ascent'] = 0;
        $handler->units['distance']       = 'm';
        $handler->units['speed']          = 'm/s';

        foreach($xml->trk->trkseg->trkpt as $point){
            
            $point_time = new DateTime($point->time);
            $point_curr['timestamp']     = $point_time->getTimestamp();
            $point_curr['position_lat']  = (1/Helper::$semic_to_deg) * $point_curr['lat']  = (float) $point->attributes()['lat'][0];
            $point_curr['position_long'] = (1/Helper::$semic_to_deg) * $point_curr['long'] = (float) $point->attributes()['lon'][0];
            $point_curr['altitude']      = (float) $point->ele;
            
            if($data_index > 0) {
                
                //Altitude
                if($point_curr['altitude'] > $point_prev['altitude']){
                    $handler->session['total_ascent'] += $point_curr['altitude'] - $point_prev['altitude'];
                }
                
                //Distance
                $delta_distance         = Helper::haversineDistance($point_curr['lat'], $point_curr['long'], $point_prev['lat'], $point_prev['long']);
                $point_curr['distance'] = $point_prev['distance'] + $delta_distance;
                
                //Speed
                $point_curr['speed'] = $delta_distance / ($point_curr['timestamp'] - $point_prev['timestamp']);
            }
            else{
                $point_curr['distance'] = $point_curr['speed'] = 0;
            }
            
            $handler->record[$data_index] = $point_prev = $point_curr;
            $data_index++;
        }

        $handler->session['total_distance']   = $point_curr['distance'];
        $handler->session['total_timer_time'] = $handler->session['total_elapsed_time'] = $point_curr['timestamp'] - $handler->session['start_time'];
        $handler->session['avg_speed']        = $handler->session['total_distance'] / $handler->session['total_timer_time'];
         
        $this->closeFile();
        
        return $this;
    }
    
    /**
     * Uzavrie súbor ak bol otvorený
     */
    protected function closeFile() {
        
        if ($this->f) {
            fclose($this->f);
        }
    }
}
