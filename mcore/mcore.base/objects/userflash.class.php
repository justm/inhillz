<?php
/**
 * Súbor obsahuje triedu Userflash
 *
 * @author Matus Macak < matus.macak@folcon.sk > 
 * @link http://www.folcon.sk/
 * @version 1.1 Mercury drop
 * @since Subor je súčasťou aplikácie od verzie 1.0
 * @package mcore.mcore\.base.objects
 *
 */

/**
 * Trieda Userflash slúži k zobrazovaniu informačných textov pre používateľa
 * 
 * @since  1.1 sú všetky správy ukladané do $_SESSION
 */
class Userflash {
    
    /**
     *
     * @var String Nazov kluca v $_SESSION, ktore nesie pole zo spravami
     */
    private $flashKey = 'mFlash';
    
    /**
     * Metóda nastaví novú správu
     * 
     * @param String $key Kľúč pod ktorým sa správa uloží do poľa
     * @param String $flashType Typ správy
     * @param String $message Text správy
     */
    public function setFlash( $key, $flashType, $message ){
        
        if( !isset( $_SESSION[$this->flashKey] ) ){
            $_SESSION[$this->flashKey] = array();
        }
        $_SESSION[$this->flashKey][$key] = (object) array( 'type' => $flashType, 'text' => $message );
    }
    
    /**
     * Metóda zistí či používateľ má priradenú správu s oznámením
     * 
     * @return boolean
     */
    public function hasFlash( $key ){
        
        if( isset( $_SESSION[$this->flashKey] ) && array_key_exists( $key, $_SESSION[$this->flashKey] ) ){
            return TRUE;
        }
        else{
            return FALSE;
        }
    }
    
    /**
     * Metóda vráti oznámenie z poľa podľa zadaného kľúča
     * 
     * @return object
     */
    public function getFlash( $key ){
        
        if( isset( $_SESSION[$this->flashKey] ) && array_key_exists( $key, $_SESSION[$this->flashKey] ) ){
            
            $flash = $_SESSION[$this->flashKey][$key];
            unset( $_SESSION[$this->flashKey][$key] );
            return $flash;
        }
        else{
            return NULL;
        }
    }
    
    /**
     * Metóda vráti celé pole oznámení
     * 
     * @return array
     */
    public function getFlashes(){
        
        if( isset( $_SESSION[$this->flashKey] ) ){
            $flashes = $_SESSION[$this->flashKey];
            unset( $_SESSION[$this->flashKey] );
            return $flashes;
        }
        else{
            return array();
        }
    }
}