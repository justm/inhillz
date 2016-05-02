<?php

namespace inhillz\components;

/**
 * ActivityParser
 * 
 * @package    inhillz\components
 * @author     Matus Macak <matus.macak@orchidsphere.com>
 * @link       http://ride.inhillz.com/
 * @version    2.0
 */
class ActivityParser {

    /**
     * @var array Priemerné a celkové údaje o aktivite
     * 
     * @todo It would be nice to have an object with properties:
     * start_time
     * avg_heart_rate
     * max_heart_rate
     * total_distance
     * avg_speed
     * total_ascent
     * avg_power
     * total_timer_time
     * total_elapsed_time
     */
    public $session;
    
    /**
     * @var array Pole momentov v priebehu tréningu s údajmi pre daný moment
     *      
     * @todo It would be nice to have an object with properties:
     * position_lat
     * position_long
     * distance
     * altitude
     * speed
     * heart_rate
     * cadence
     * temperature
     * power
     * ...
     */
    public $record;
    
    /**
     * @var array Fyzikálne jednotky pre jednotlivé údaje
     */
    public $units;
    
    /**
     * @var string Chybový výpis
     */
    public $error = '';
    
    /**
     * Inicializuje parser a podľa typu súboru načíta data
     * @param string $file_name
     * @param bool $read_header
     * @param char $delimeter
     * @param char $enclosure
     */
    public function __construct($file_name) {
        
        $extension = pathinfo($file_name, PATHINFO_EXTENSION);
        
        switch(strtolower($extension)){
            case 'csv':
                $parser = new CsvActivityParser($file_name);
            break;
            case 'gpx': 
                $parser = new GpxActivityParser($file_name);
            break;
        }
        
        $parser->parse($this);
    }
    
    /**
     * 
     * @return stdClass
     */
    public function getSession() {
        return (object) $this->session;
    }

    /**
     * 
     * @return stdClass
     */
    public function getUnits() {
        return (object) $this->units;
    }
    
    /**
     * 
     * @return array
     */
    public function getRecord() {
        return $this->record;
    }

    /**
     * Returns every N-th element of record data, where N is specified by $step
     * @param int $step
     * @return array
     */
    public function getRecordStrips($step) {
        
        $keys   = range(0, count($this->record), $step);
        return array_values(array_intersect_key($this->record, array_combine($keys, $keys)));
    }
}
