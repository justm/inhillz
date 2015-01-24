<?php
/**
 * Súbor obsahuje triedu Helper
 *
 * @author Matus Macak < matus.macak@folcon.sk > 
 * @link http://www.folcon.sk/
 * @version 2.0
 * @since Subor je súčasťou aplikácie od verzie 2.0
 * @package controllers
 * 
 */

/**
 * Trieda pre pomocné operácie
 */
class Helper extends McontrollerCore{
    
    /**
     * Metoda uploaduje jeden súbor do zvoleného podadresára v adresári uploads.
     * @param $_FILE $file
     * @param String $subdirectory Podaresár, alebo aj viac úrovní
     * @param array $supported Pole s podporovanými typmi
     * @param array $error Flag, v ktorom je uvedená chybová hláška v prípade že upload zlyhá
     * 
     * @return String|FALSE cesta k súboru, FALSE ak sa súbor upload zlyhá
     */
    public static function uploadFile( $file, $subdirectory, $supported, &$error ){
                
        $uploadPath = 'uploads/' . $subdirectory . DIRECTORY_SEPARATOR;
        
        $exp        = explode( '.', $file['name'] );
        $extension  = array_pop( $exp );
                 
        if( array_search( strtolower($extension), $supported ) === FALSE ){
            $error[] = Mcore::t( '{FILE} is not supported file type', 'global', array( '{FILE}' => $file['name'] ) );
            return FALSE;
        }
        else if( move_uploaded_file( $file['tmp_name'], MCORE_PROJECT_PATH . $uploadPath . $file['name'] ) ){
                  
            return MCORE_PROJECT_PATH . $uploadPath . $file['name']; //Vytvorenie URL adresy
        }
        $error[] = Mcore::t( 'Uploading Failed for file {FILE}', 'global', array( '{FILE}' => $file['name'] ) );
        return FALSE;
    }
    
    /**
     * Hash funkcia, pre tvorbu uniformných stringov
     * @param String $string
     * @return String
     */
    public static function getHash( $string ){
        
        return sha1( "RW8DLG" . $string );
    }
}