<?php
/**
 * Súbor obsahuje triedu Captcha
 *
 * @author Matus Macak < matus.macak@folcon.sk > 
 * @link http://www.folcon.sk/
 * @version 1.2 Cute Genie
 * @since Subor je súčasťou aplikácie od verzie 1.0
 * @package mcore.mcore\.base.objects
 * 
 */

/**
 * McaptchaModule slúži na generovanie náhodného captcha kódu, používaného
 * k strojovému odosielaniu formuláru na stránke.
 * 
 *  
 */
class Captcha{
            
    /**
     * Konštruktor
     */
    public function __construct() {
        
    }
    
    /**
     * Metóda generuje náhodný kód a poznačí ho do poľa $_SESSION pod indexom 'mcaptcha'
     */
    public function generateCaptcha(){
        
        $string = '';  
        for ($i = 0; $i < 4; $i++) {  
            $string .= chr( rand( 97, 122 ) );  
        }  
        $_SESSION['mcaptcha'] = $string;
        
        $image = imagecreatetruecolor( 110, 50 );  
        $color = imagecolorallocate( $image, 68, 68, 68 ); // text color 
        $white = imagecolorallocate( $image, 255, 255, 255 ); 
        
        imagefilledrectangle( $image, 0, 0, 200, 100, $white );  
        imagettftext( $image, 25, rand( -5, 5 ),15, 40, $color, 
                      MCORE_BASE_PATH . "mcore.base/objects/includes/captcha_font.ttf", $_SESSION['mcaptcha'] );  
        
        header( "Content-type: image/png" );  
        imagepng( $image ); 
    }
    
    /**
     * Kontrola captcha kódu zadaného používateľom
     * @param String $string Reťazec, ktorý sa kontroluje
     * @return boolean 
     */
    public function checkCaptcha( $string ){
        
        if( $string == $_SESSION['mcaptcha'] ){
            unset( $_SESSION['mcaptcha'] );
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * Metoda skontroluje ci je pri potvrdení formuláru požadovaný captcha kód
     * @return boolean
     */
    public function requiredCaptcha(){
        
        if( isset( $_SESSION['mcaptcha'] ) ){
            return TRUE;
        }
        return FALSE;
    }
}
