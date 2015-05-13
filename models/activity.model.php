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

    /**
     * Načítanie dát s príznakom  'record' z CSV súboru s
     * @param String $data_file Názov súboru, ktorý sa načíta
     * @return array
     */
    public static function get_record( $data_file ){
              
        $data_model = new Csv_activity_parser(MCORE_PROJECT_PATH . 'uploads/activities_data/' . $data_file);
        
        return $data_model->get_record();
    }
}
