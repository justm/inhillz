<?php
/**
 * Súbor obsahuje triedu Csv_activity_parser
 *
 * @author Matus Macak < matus.macak@folcon.sk > 
 * @link http://www.folcon.sk/
 * @version 2.0
 * @since Subor je súčasťou aplikácie od verzie 2.0
 * @package extend
 * 
 */

require MCORE_APP_PATH . 'libraries/CSVparser.php';

/**
 * Trieda pre načítanie a ukladanie dátami o tréningu z CSV súboru
 */
class Csv_activity_parser extends CSVparser{
    
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
    public function get_session() {
        return (object) $this->session;
    }

    /**
     * 
     * @return array
     */
    public function get_record() {
        return $this->record;
    }

    /**
     * 
     * @return stdClass
     */
    public function get_units() {
        return (object) $this->units;
    }
}

                