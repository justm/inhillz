<?php

namespace inhillz\components;

/**
 * Trieda Csv_activity_parser pre načítanie a ukladanie dátami o tréningu z CSV súboru
 *
 * @package    inhillz\components
 * @author     Matus Macak <matus.macak@orchidsphere.com>
 * @link       http://ride.inhillz.com/
 * @version    2.0
 * 
 */
class Csv_activity_parser extends \orchidsphere\dataexchange\CSVparser{
    
    /**
     * Priemerné a celkové údaje o aktivite
     * @var array 
     */
    private $session;
    
    /**
     * Pole momentov v priebehu tréningu s údajmi pre daný moment
     * @var array 
     */
    private $record;
    
    /**
     * Fyzikálne jednotky pre jednotlivé údaje
     * @var array 
     */
    private $units;
    
    /**
     * @var bool Non-verbose
     */
    protected $log_enable = FALSE;
    
    /**
     * Initialize parser and read file
     * @param string $file_name
     * @param bool $read_header
     * @param char $delimeter
     * @param char $enclosure
     */
    public function __construct($file_name, $read_header = TRUE, $delimeter = ',', $enclosure = '"') {
        
        parent::__construct($file_name, $read_header, $delimeter, $enclosure);
        $this->openFile()->readFile();
    }

    /**
     * Vytiahnutie dát zo súboru a rozdelenie do potrebnej štruktúry
     * @return Csv_activity_parser
     */
    protected function readFile(){
        
        $field1_start = array_search('Field 1', $this->header);
        $data_index   = 0;
        
        while( ($row = fgetcsv($this->f, 0, $this->delimeter, $this->enclosure)) != NULL ){
            
            if( $row[2] == 'record' && $row[0] == 'Data' ){   
                $i = $field1_start;
                
                while( $i+2 <= count($row) ){
                    $this->record[$data_index][$row[$i]] = $row[$i+1];
                    $this->units[$row[$i]] = $row[$i+2];
                    $i+=3;
                }
                $data_index++;
            }
            elseif( $row[2] == 'session' && $row[0] == 'Data'){
                $i = $field1_start;
                while( $i+2 <= count($row) ){
                    $this->session[$row[$i]] = $row[$i+1];
                    $i+=3;
                }
            }   
        }
        
        $this->closeFile();
        
        return $this;
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
            
    /**
     * 
     * @return stdClass
     */
    public function getUnits() {
        return (object) $this->units;
    }

    /**
     * @inheritdoc
     */
    protected function modify() {
        
    }
}

                