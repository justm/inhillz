<?php

namespace inhillz\models;

use inhillz\components\ActivityParser;
use orchidphp\Orchid;

/**
 * Trieda ActivityModel predstavuje dátový model údajov zo športového merača
 *
 * @package    inhillz\models
 * @author     Matus Macak <matus.macak@orchidsphere.com>
 * @link       http://ride.inhillz.com/
 * @version    2.0
 * 
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
              
        Orchid::base()->db->executeQuery(
            "SELECT * FROM " . self::$table . " WHERE id_training_entry = {$id}"
        );
        
        if( Orchid::base()->db->getNumRows() > 0 ){
            return Orchid::base()->db->getArrayRows();
        }
        else{
            $data_model  = new ActivityParser(PROJECT_PATH . 'uploads/activities_data/' . $data_file);
            $data_stream = $data_model->getRecord();
            
            //self::save_record($data_stream, $id);
            
            return $data_stream;
        }
    }
    
    /**
     * Uloží dáta do databázy
     * @param Array $data_stream
     */
    public static function save_record( $data_stream, $id_training_entry ){
        
        return;
        
        Orchid::base()->getObject('db')->executeQuery( "SHOW columns FROM `" . self::$table . "`" );
        $_r      = Orchid::base()->getObject('db')->getArrayRows();
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
        
        $fields = trim($fields, ','); //odstránenie koncového znaku ","
        $values = trim($values, ','); 
        $update = trim($update, ','); 
        
        $insert = "INSERT INTO " . self::$table . " ({$fields}) VALUES {$values} ON DUPLICATE KEY UPDATE {$update}";
        
        Orchid::base()->db->executeQuery($insert);
    }
}
