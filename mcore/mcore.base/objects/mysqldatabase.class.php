<?php
/**
 * Súbor obsahuje triedu Mysqldatabase
 *
 * @author Matus Macak < matus.macak@folcon.sk > 
 * @link http://www.folcon.sk/
 * @version 1.2 Cute Genie
 * @since Subor je súčasťou aplikácie od verzie 1.0
 * @package mcore.mcore\.base.objects
 *
 */

/**
 * Trieda pre správu prístupu k databáze: základná abstrakcia
 */
class Mysqldatabase {
    
    /**
     * @var array Umožňuje viac spojení s databázou, pričom každé spojenie
     * sa uloží vo forme prvku poľa
     */
    private $connections = array();
    
    /**
     * @var int Určuje, ktoré spojenie sa má použiť = používané spojenie
     */
    private $activeConnection = 0;
    
    /**
     * @var array Prevedené dotazy, ktoré sa uložili do medzipamäte,
     */
    private $queryCache = array();
    
    /**
     * @var Integer Počet vykonaných dotazov do databázy
     */
    private $queryCounter = 0;
    
    /**
     * @var MYSQLI_RESULT Záznam o poslednom dotaze
     */
    private $last;
    
    /**
     * Konštruktor databázového objektu
     */
    public function __construct() { }
    
    /**
     * Vytvorenie nového spojenia s databázou
     * 
     * Funkcie metódy by mohol prebrať aj konštruktor, takto však je možne uchovávať
     * viacero spojení v jednom objekte
     * @param String $host Názov hostiteľa
     * @param String $user Používateľské meno
     * @param String $password Heslo
     * @param String $database Názov databázy
     * @return int Index v poli spojení s DB
     */
    public function newConnection( $host, $user, $password, $database ){
        
        $this->connections[] = new mysqli( $host, $user, $password, $database );
        $connection_id = count( $this->connections ) - 1;
        $this->connections[$connection_id]->set_charset("utf8");
        if (mysqli_connect_errno() ) {
            throw new Exception( $this->connections[$connection_id]->error );
        }
        return $connection_id;
    }
    /**
     * Metóda umožňuje nastaviť aktívne pripojenie
     * @param int
     * @return void 
     */
    public function setActiveConnection( $activeConnection ) {
        $this->activeConnection = $activeConnection;
    }
    
    /**
     * Metóda ukončí aktívne spojenie
     * @return void
     */
    public function closeConnection() {
        $this->connections[$this->activeConnection]->close();
    }
    
    /**
     * Metóda vykoná databázový dotaz
     * @param String $queryStr Dotaz
     * @return boolean Informácia o úspešnosti prevedenia dotazu
     */
    public function executeQuery( $queryStr ) {
        
        //echo ++$this->queryCounter . '<br />';
        //echo $queryStr.'<br /><br />';
        if( !$result = $this->connections[$this->activeConnection]->query( $queryStr ) ) {
            throw new Exception( $this->connections[$this->activeConnection]->error 
                                . ' Executed query: ' . $queryStr );
        }
        else {
            $this->last = $result;
            return true;
        }
    }
    
    /**
     * Metóda vráti AUTO_INCREMENT ID naposledy vloženého (obnoveného) záznamu
     * @return mixed 
     */
    public function getLastInsertID(){
        
        return $this->connections[$this->activeConnection]->insert_id;
    }
    
    /**
     * Metóda vráti pole výsledkov dotazu. 
     * 
     * Metóda vráti pole v ktorom každá hodnota predstavuje riadok z výsledku 
     * vo formáte asociatívneho poľa 
     * @return array
     */
    public function getArrayRows() {
        
        $ret = array();
        while ( $row = $this->last->fetch_assoc() ) {
            $ret[] = $row;
        }
        return $ret;
    }
    
     /**
     * Metóda vráti pole výsledkov dotazu. 
     * 
     * Metóda vráti pole v ktorom každá hodnota predstavuje riadok z výsledku 
     * vo formáte objektu
     * @return array Pole objektov
     */
    public function getObjectRows() {
        
        $ret = array();
        while ( $row = $this->last->fetch_object() ) {
            $ret[] = $row;
        }
        return $ret;
    }
    
    /**
     * Metóda vráti počet riadkov výsledku z posledného vykonaného dotazu
     * @return int
     */
    public function getNumRows(){
	    return $this->last->num_rows;
    }
    
    /**
     * Metóda vráti počet riadkov ovplyvnených posledných dotazom
     * @return int Počet riadkov
     */
    public function affectedRows() {
        return $this->last->affected_rows;
    }
    
    /**
     * Uloží výsledok dotazu do medzipamäte pre neskoršie spracovanie
     * @param String $queryStr Databázový dotaz
     * @return int Index výsledku v medzipamäti $this->queryCache
     */
    public function cacheQuery( $queryStr ) {
        
        if( !$result = $this->connections[$this->activeConnection]->query( $queryStr ) ) {
            throw new Exception( $this->connections[$this->activeConnection]->error );
        }
        else {
            $this->queryCache[] = $result;
            return count($this->queryCache) - 1;
        }
    }
    
    /**
     * Metóda vráti pole výsledkov z dotazu uloženého v medzipamäti. 
     * 
     * Metóda vráti pole v ktorom každá hodnota predstavuje riadok z výsledku 
     * vo formáte asociatívneho poľa.
     * @param int Index dotazu v Cache
     * @return array
     */
    public function getArraysFromCache( $cache_id ) {
        
        $ret = array();
        while ( $row = $this->queryCache[$cache_id]->fetch_array() ) {
            $ret[] = $row;
        }
        return $ret;
    }
    
     /**
     * Metóda vráti pole výsledkov z dotazu uloženého v medzipamäti. 
     * 
     * Metóda vráti pole v ktorom každá hodnota predstavuje riadok z výsledku 
     * vo formáte objektu
     * @param int Index dotazu v Cache
     * @return array Pole objektov
     */
    public function getObjectsFromCache( $cache_id ) {
        $ret = array();
        while ( $row = $this->queryCache[$cache_id]->fetch_object() ) {
            $ret[] = $row;
        }
        return $ret;
    }
    
    /**
     * Získa počet riadkov, ktoré daný výsledok v medzipamäti obsahuje
     * @param int $cache_id Index výsledku uloženého v medzi pamäti
     * @return int Počet riadkov výsledku
     */
    public function getNumsFromCache( $cache_id ) {
        return $this->queryCache[$cache_id]->num_rows;
    }
    
    /**
     * Metóda vytvorí na základe parametrov názvu tabuľky, podmienky a limitu
     * dotaz na odstránenie záznamu a vykoná ho
     * 
     * @param String $table Tabuľka z ktorej sa záznam odstráni
     * @param String $condition Podmienka pre odstránenie
     * @param int $limit Počet riadkov, ktoré sa majú odstrániť
     * @return Boolean
     */
    public function deleteRecords( $table, $condition, $limit = '1' ) {
        
        $limit = ( $limit == '' ) ? '' : ' LIMIT ' . $limit;
        
        $delete = "DELETE FROM {$table} WHERE {$condition} {$limit}";
        $this->executeQuery( $delete );
        
        return TRUE;
    }
    
    /**
     * Metóda aktualizuje záznamy v databáze
     * 
     * @param String $table Názov tabuľky
     * @param array $chages Pole zmenie stĺpec => hodnota
     * @param String $condition Podmienka
     * @param array $default Pole stĺpcov ktorá majú v DB prednastavenú DEFAULT hodnotu, použije sa ak je NULL
     * @return bool
     */
    public function updateRecords( $table, $changes, $condition = '', $defaults = array() ) {
        
        $update = "UPDATE `{$table}` SET ";
        foreach ( $changes as $f => $v ){
            
            if( $v === NULL && in_array( $f, $defaults )) {
                $update .= "`{$f}` = DEFAULT, ";
            }
            elseif( $v === NULL  ){
                $update .= "`{$f}` = NULL, ";
            }
            elseif ( is_numeric( $v ) && ( intval( $v ) == $v ) 
                    && ( substr( $v, 0, 1) != '0' && substr( $v, 0, 1) != '+' ) ) {
                $update .= "`{$f}` = {$v}, ";
            }
            else {
                $update .= "`{$f}` = '{$this->sanitizeData( $v )}', ";
            }
        }
        //odstránenie koncového znaku ","
        $update = substr($update, 0, -2);
        if( $condition != '' ) {
            $update .= " WHERE " . $condition;
        }
        //echo $update;
        $this->executeQuery( $update );
        
        return true;
    }
    
    /**
     * Metóda vytvorí na základe parametra tabuľka a pole dát dotaz pre vloženie 
     * parametrov a vykoná ho
     * 
     * @param String $table Názov tabuľky
     * @param array $data Pole hodnôt vo forme stĺpec => hodnota
     * @param array $default Pole stĺpcov ktorá majú v DB prednastavenú DEFAULT hodnotu, použije sa ak je NULL
     * @return bool
     */
    public function insertRecords( $table, $data, $defaults = array() ) {
        
        //inicializácia premenných pre uloženie stĺpcov a hodnôt
        $fields = '';
        $values = '';
        
        //vyplnenie premennych
        foreach ( $data as $f => $v) {
            $fields .= "`{$f}`, ";
            
            if( $v === NULL && in_array( $f, $defaults )) {
                $values .= "DEFAULT, ";
            }
            elseif( $v === NULL  ){
                $values .= "NULL, ";
            }
            elseif ( is_numeric( $v ) && ( intval( $v ) == $v )
                    && ( substr( $v, 0, 1) != '0' && substr( $v, 0, 1) != '+' ) ) {
                $values .= "{$v}, ";
            }
            else {
                $values .= "'{$this->sanitizeData( $v )}', ";
            }
        }
        //odstránenie koncového znaku ","
        $fields = substr($fields, 0, -2);
        $values = substr($values, 0, -2);
        
        $insert = "INSERT INTO `{$table}` ({$fields}) VALUES ({$values})";
        //echo $insert;
        $this->executeQuery( $insert );
        
        return true;
    }
    
    /**
     * Vloženie a update on duplicate key pre pole zaznamov
     * 
     * @param String $table Názov tabuľky
     * @param array $rows Pole záznamov, každá hodnota predstavuje dalsie pole 
     *      A.K.A jeden riadok DB vo forme stĺpec => hodnota
     * @param array $default Pole stĺpcov ktorá majú v DB prednastavenú DEFAULT hodnotu, použije sa ak je NULL
     * @param array $not_update_array Stĺpce ktoré sa neaktualizuju
     * @return bool
     */
    public function multipleInsertUpdate( $table, $rows, $defaults = array(), $not_update_array = array()) {
        
        //** Zistí stĺpce tabulky, hodnoty ktoré nepatria do tabuľky bude zahadzovať
        Mcore::base()->getObject('db')->executeQuery( "SHOW columns FROM `{$table}`" );
        $_r      = Mcore::base()->getObject('db')->getArrayRows();
        $columns = array_column( $_r, 'Field' );
        
        //** Inicializácia premenných pre uloženie stĺpcov a hodnôt
        $fields      = '';
        $finalValues = '';
        $update      = '';
        
        if( !empty( $rows ) ){
            $fieldsCheck = 0;
            foreach ( $rows as $data ){
                $fieldsCheck++; 
                $values = '';
                
                //vyplnenie premennych 
                foreach ( $data as $f => $v) {
                    if ( array_search( $f, $columns) !== FALSE ){ //Preskakuje stĺpce, ktoré nie sú v tabuľke
                        if( $fieldsCheck == 1 ) { // Definovanie stlpcov prebehne iba nad prvym polom
                            $fields .= "`{$f}`, ";

                            if ( array_search( $f, $not_update_array ) == FALSE ) {
                                $update .= "`{$f}` = VALUES(`{$f}`), ";
                            }
                        }
                        if( $v === NULL && in_array( $f, $defaults )) {
                            $values .= "DEFAULT, ";
                        }
                        elseif( $v === NULL  ){
                            $values .= "NULL, ";
                        }
                        elseif ( is_numeric( $v ) && ( intval( $v ) == $v ) ) {
                            $values .= "{$v}, ";                    
                        }
                        else {
                            $values .= "'{$this->sanitizeData($v)}', ";
                        }
                    }
                }
                $finalValues .= '(' . substr( $values, 0, -2 ) . '), ';
                
                //odstránenie koncového znaku ","
                if( $fieldsCheck == 1 ) { // Definovanie stlpcov prebehne iba nad prvym polom
                    $fields = substr( $fields, 0, -2 );
                    $update = substr( $update, 0, -2 );
                }
            }  
            $finalValues = substr( $finalValues, 0, -2 ); //odstránenie koncového znaku ","
            $insert      = "INSERT INTO {$table} ({$fields}) VALUES {$finalValues} ON DUPLICATE KEY UPDATE {$update}";
            //echo $insert;
            $this->executeQuery( $insert );
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * Vykoná dotaz $query do databázy a vráti pole hodnôt zo špecifikovaného stĺpca $column oindexované kľúčom $key
     * 
     * @param String $query
     * @param String $key
     * @param String $column
     * @return type
     */
    public function queryPairs( $query, $key, $column ){
        
        $this->executeQuery( $query );
        if( $this->getNumRows() != 0 ) {
            $rows = $this->getArrayRows();
        }
        return array_column( $rows, $column, $key );
    }
    
    /**
     * Vráti jednu hodnotu z databázy
     * 
     * @param String $query
     * @param String $column
     * @param String $is_integer Definuje či má byť návratová hodnota integer
     * @return type
     */
    public function queryValue( $query, $column, $is_integer = FALSE ){
        
        $this->executeQuery( $query );
        if( $this->getNumRows() != 0 ) {
            $row = $this->getArrayRows()[0];
        }
        if( array_key_exists( $column, $row ) !== FALSE ){
            return ( $is_integer )? intval($row[$column]) : $row[$column];
        }
        return NULL;
    }
    
    /**
     * Generuje SELECT výraz, ktorý hodnoty definované vo vstupnom poli $fields vráti vo výsledku dotazu ako JSON
     * @param array $fields, $key => $value, $key=požadovaný JSON attribute, $value=Názov stĺpca z DB query
     * @return string
     */
    public function concatJSON( $fields ) {

            $str = "CONCAT('[',GROUP_CONCAT(CONCAT('{\"";
            $i   = 0 ;
            $l   = count($fields);
                        
            foreach ( $fields as $key => $column ){
                
                ++$i;
                $str.= $key . "\":','\"'," . $column;
                
                if ( $i != $l ) {
                        $str.= ",'\",\"";
                }
            }
            $str.= ",'\"}')),']')";
            return $str;
    }

    /**
     * Metóda vyčistí dáta tak aby ich bolo možné vložiť do SQL dotazu
     * @param String $data Dáta, ktoré sa majú vyčistiť
     * @return String vyčistené dáta
     */
    public function sanitizeData( $data ){
        
        return $this->connections[$this->activeConnection]->real_escape_string( $data );
    }
    
    /**
     * Deštruktor objektu, ukončí všetky otvorené spojenia s databázovým systémom
     */
    public function __destruct() {
        
        foreach ( $this->connections as $connection ){
            $connection->close();
        }
    }
}