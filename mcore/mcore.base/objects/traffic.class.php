<?php
/**
 * Súbor pripojí McoreTraffic Modul
 *
 * @author Matus Macak < matusmacak@justm.sk > 
 * @link http://justm.sk/
 * @version 1.2 Cute Genie
 * @since Subor je súčasťou aplikácie od verzie 1.2
 * @package mcore.mcore\.base.objects
 *
 */

class Traffic extends MmodelCore{
      
    /**
     * Metóda vráti statický model triedy
     * @param string Názov triedy
     * @return McoreTrafficModel
     */
    public static function model( $className = __CLASS__ ){
        return parent::model( $className );
    }
    
    /**
     * Metóda vráti názov tabuľky v DB, ku ktorej prislúcha tento model
     * @return String Názov tabuľky
     */
    public function table(){
        return 'mcore_traffic';
    }
    
    /**
     * Vráti navštevy, jedinečne navštevy, zobrazenia stranky
     * @param String $date
     * @return Object (page_views, unique_visitors, visits)
     */
    public function getTraffic( $date = NULL ){
        if( !isset( $date ) ){
            $date = date('Y-m-d',strtotime("last month"));
        }
        $sql = "SELECT count(id) as page_views, count(distinct(address_ip)) as unique_visitors, "
             . "(SELECT count(id) FROM {$this->table()} WHERE referer_domain not like '%" . str_replace(array('http://', 'https://'), '', ROOT_URL)."%') as visits "   
             . "FROM {$this->table()} WHERE date_time > '{$date}'";
        
        Mcore::base()->db->executeQuery( $sql );
        return Mcore::base()->db->getObjectRows()[0];
    }
    
    /**
     * Vráti počet návštev
     * @param String $date
     * @return array of Objects
     */
    public function getVisits( $date = NULL ){
        if( !isset( $date ) ){
            $date = date('Y-m-d',strtotime("last month"));
        }
        $sql = "SELECT date_format(`date_time`,'%d. %m. %Y') as date_time, count(id) as visits "
             . "FROM {$this->table()} WHERE date_time > '{$date}' "
             . "AND referer_domain not like '%" . str_replace(array('http://', 'https://'), '', ROOT_URL)."%' "
             . "GROUP BY DAY(`date_time`)";
        
        Mcore::base()->db->executeQuery( $sql );
        return Mcore::base()->db->getObjectRows();
    }
    
    /** 
     * Metóda, ktorá vráti pole definovaných označení pre jednotlivé atribúty modelu
     */
    public function labels() {
        
        return array( );
    }

    /**
     * Metóda, ktorá vráti pole definovaných pravidiel pre jednotlivé atribúty modelu
     */
    public function rules() {
        
        return array( );
    }
}