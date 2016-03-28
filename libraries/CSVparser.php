<?php
/**
 * Parser pre súbory typu CSV
 *
 * @author Matus Macak < matus.macak@folcon.sk > 
 * @link http://www.folcon.sk/
 * @version 2.0
 * @since Subor je súčasťou aplikácie od verzie 2.0
 * @package libraries
 *
 */

class CSVparser {
    
    /**
     * @var String $file_name Cesta k súboru
     */
    protected $filepath = NULL;
    
    /**
     * @var resource $f Deskriptor súboru 
     */
    protected $f = FALSE;
    
    /**
     * @var String $delimiter Oddelovač stĺpcov v CSV
     */
    protected $delimeter = NULL;
    
    /**
     * @var String $enclosure Uzavretie stĺpcov, ktoré je použité v súbore - úvodzovky alebo pod.
     */
    protected $enclosure = NULL;
    
    /**
     * @var bool Obmedzuje čítanie, resp. nečítanie hlavičky súboru 
     */
    protected $read_header = TRUE;
    
    /**
     * @var array $header Pole definujúce názvy stĺpcov v hlavičke
     */
    protected $header = NULL;
                
    /**
     * @var int $col_count Počet stĺpcov
     */
    protected $col_count = 0;
    
    /**
     * @var array $data Pole dát získaných zo súboru 
     * Jeden riadok súboru predstavuje jedno podpole indexované hodnotami z mapped_header
     */
    protected $data = array(); 
    
    /**
     * Inicializácia CSV parsera.
     * 
     * @param string $filepath
     * @param bool $read_header
     * @param char $delimeter
     * @param char $enclosure
     */
    public function __construct( $filepath, $read_header = TRUE, $delimeter = ',', $enclosure = '"' ) {
                        
        //$this->logMsg("Reading start. Memory usage = " . memory_get_usage() . ' bytes');
        
        $this->delimeter   = $delimeter;
        $this->enclosure   = $enclosure;
        $this->filepath    = $filepath;
        $this->read_header = $read_header;
        
        $this->openFile()->readFile();
        
        //$this->logMsg("Reading end. Memory usage = " . memory_get_usage() . ' bytes');
    }
    
    /**
     * Deštruktor CSV parsera, zatvorí súbor
     */
    function __destruct() {
        
       $this->closeFile();
    }
                
    /**
     * Otvorí súbor a pokúsi sa prečítať hlavičku
     */
    protected function openFile() {
        
        $this->closeFile();
        
        $this->f = fopen( $this->filepath, "r" );

        if ( $this->f === FALSE ) {
            $this->logMsg( "Can't open file: {$this->filepath}" );
        }
        elseif ( $this->read_header ) {
            $this->readHeader();
        }
        return $this;
    }
       
    /**
     * Po otvorení súboru, načíta hlavičku a zistí počet stĺpcov. Automaticky prejde na ďalší riadok.
     */
    protected function readHeader() {

        $this->header = fgetcsv($this->f, 0, $this->delimeter, $this->enclosure);

        if ( $this->header != NULL ) {
            $this->col_count = count( $this->header );
        }
        else {
            $this->logMsg( "Reading header in file {$this->filepath} failed!" );
        }
    }
    
    /**
     * Načíta celý súbor
     * @return \CSVparser
     */
    protected function readFile(){
        
        while( ($row = fgetcsv($this->f, 0, $this->delimeter, $this->enclosure)) != NULL ){
            //$count = min(count($this->mapped_header), count($row)); //prevents array_combine with different lengths
            //$this->data[] = array_combine( array_slice( $this->mapped_header, 0, $count ), array_slice( $row, 0, $count ) );             
            $this->data[] = $row;          
        }
        
        $this->closeFile();
        
        return $this;
    }
        
    /**
     * Uzavrie súbor ak bol otvorený
     */
    protected function closeFile() {
        		
        if ( $this->f ) {
            fclose($this->f);
            $this->f = NULL;
        }
    }
    
    /**
     * Výpis logov
     * @param String $msg
     */
    protected function logMsg( $msg ){
        
        list($usec, $sec) = explode(" ", microtime()); 
        "<br/>[".date("H:i:s", $sec) . "." . $usec ."] ".$msg."\n";
    }
}