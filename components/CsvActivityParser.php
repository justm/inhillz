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
class CsvActivityParser extends \orchidsphere\dataexchange\CSVparser{
    
    /**
     * @var bool Non-verbose
     */
    protected $log_enable = FALSE;
    
    /**
     * Inicializuje parser 
     * @param string $file_name
     * @param bool $read_header
     * @param char $delimeter
     * @param char $enclosure
     */
    public function __construct($file_name, $read_header = TRUE, $delimeter = ',', $enclosure = '"') {
        
        parent::__construct($file_name, $read_header, $delimeter, $enclosure);
    }

    /**
     * Spustí čítanie súboru
     * @param type $handler
     */
    public function parse(ActivityParser $handler){
        
        $this->openFile()->readFileRecord($handler);
    }
    
    /**
     * Vytiahnutie dát zo súboru a rozdelenie do potrebnej štruktúry
     * @return CsvActivityParser
     */
    protected function readFileRecord($handler){
        
        if ( $this->f === FALSE ) {
            $handler->error = 'Data file is corrupted and cannot be uploaded';
            return;
        }
        
        $field1_start = array_search('Field 1', $this->header);
        $data_index   = 0;

        while( ($row = fgetcsv($this->f, 0, $this->delimeter, $this->enclosure)) != NULL ){
            
            if( $row[2] == 'record' && $row[0] == 'Data' ){   
                $i = $field1_start;
                
                while( $i+2 <= count($row) ){
                    $handler->record[$data_index][$row[$i]] = $row[$i+1];
                    $handler->units[$row[$i]] = $row[$i+2];
                    $i+=3;
                }
                $data_index++;
            }
            elseif( $row[2] == 'session' && $row[0] == 'Data'){
                $i = $field1_start;
                while( $i+2 <= count($row) ){
                    $handler->session[$row[$i]] = $row[$i+1];
                    $i+=3;
                }
            }   
        }
        
        $handler->session['start_time'] += EPOCH_TIMESTAMP_OFFSET;
        
        $this->closeFile();
        
        return $this;
    }
    
    /**
     * @inheritdoc
     */
    protected function modify() {
        
    }
}

                