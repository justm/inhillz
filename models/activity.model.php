<?php
/**
 * Súbor obsahuje triedu ActivityModel 
 *
 * @author Matus Macak < matus.macak@folcon.sk > 
 * @link http://www.folcon.sk/
 * @version 2.0
 * @since Subor je súčasťou aplikácie od verzie 2.0
 * @package models
 * 
 */

/**
 * Trieda ActivityModel predstavuje dátový model údajov zo športového merača
 */
class ActivityModel {

    private static $table = 'training_entry_record';
    
    /**
     * Načítanie dát s príznakom  'record' z CSV súboru s
     * @param Int $id ID tréningu, ktorého údaje sa majú načítať
     * @param String $data_file Názov súboru, ktorý sa načíta
     * @return array
     */
    public static function get_record( $id, $data_file ){
              
        Mcore::base()->db->executeQuery(
            "SELECT * FROM " . self::$table . " WHERE id_training_entry = {$id}"
        );
        
        if( Mcore::base()->db->getNumRows() > 0 ){
            return Mcore::base()->db->getArrayRows();
        }
        else{
            $data_model  = new Csv_activity_parser(MCORE_PROJECT_PATH . 'uploads/activities_data/' . $data_file);
            $data_stream = $data_model->get_record();
            
            //self::save_record($data_stream, $id);
            
            return $data_stream;
        }
    }
    
    /**
     * Uloží dáta do databázy
     * @param Array $data_stream
     */
    public static function save_record( $data_stream, $id_training_entry ){
        
        Mcore::base()->getObject('db')->executeQuery( "SHOW columns FROM `" . self::$table . "`" );
        $_r      = Mcore::base()->getObject('db')->getArrayRows();
        $columns = array_column( $_r, 'Field' );
        
        $pattern = array_fill_keys($columns, "NULL");
        $pattern['id_training_entry'] = $id_training_entry;   
                
        foreach ($columns as $col){
            $fields .= "`{$col}`,";
            $update .= "`{$col}` = VALUES(`{$col}`),";
        }
        
        foreach ( $data_stream as $row ){
            unset($row['']);
            $values .= '(' . implode(',', array_merge($pattern, $row)) . '),';
        }
        
        $fields = substr( $fields, 0, -1 ); //odstránenie koncového znaku ","
        $values = substr( $values, 0, -1 ); 
        $update = substr( $update, 0, -1 ); 
        
        $insert = "INSERT INTO " . self::$table . " ({$fields}) VALUES {$values} ON DUPLICATE KEY UPDATE {$update}";
        
        Mcore::base()->db->executeQuery($insert);
    }
}
