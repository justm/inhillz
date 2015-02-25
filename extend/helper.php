<?php
/**
 * Súbor obsahuje triedu Helper
 *
 * @author Matus Macak < matus.macak@folcon.sk > 
 * @link http://www.folcon.sk/
 * @version 2.0
 * @since Subor je súčasťou aplikácie od verzie 2.0
 * @package extend
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
        $error[] = Mcore::t( 'Uploading failed for file {FILE}', 'global', array( '{FILE}' => $file['name'] ) );
        return FALSE;
    }
    
    /**
     * Konverzia jednotiek
     * @param mixed $value
     * @param String $in_unit
     * @param String $out_unit
     * @return mixed
     */
    public static function convertUnits( $value, $in_unit, $out_unit ){
                
        $conversion = array(
            'm/s' => array('km/h' => 3.6,
                           'mph' =>  2.23693629
                    ),
            'm'   => array('km' => 0.001,
                           'mi' => 0.00062137),
        );
        
        if( isset($conversion[$in_unit]) && isset($conversion[$in_unit][$out_unit]) ){
            
            return $value * $conversion[$in_unit][$out_unit];
        }
        
        return $value;
    }
    
    /**
     * Hash funkcia, pre tvorbu uniformných stringov
     * @param String $string
     * @return String
     */
    public static function getHash( $string ){
        
        return sha1( "RW8DLG" . $string );
    }
    
    public static function echoAlert( $message, $type ){
        
        echo
            '<div class="alert ' . $type . '">' . $message . '</div>';
    }
}