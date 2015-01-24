<?php
/**
 * Súbor obsahuje triedu AdminController
 *
 * @author Matus Macak < matus.macak@folcon.sk > 
 * @link http://www.folcon.sk/
 * @version 2.0
 * @since Subor je súčasťou aplikácie od verzie 2.0
 * @package controllers
 * 
 */

/**
 * Ovládač AdminController 
 */
class AdminController extends McontrollerCore{
    
    /**
     * Metóda definuje prístupové práva k metódam. Každá metóda, ktorá má prejsť kontrolou práv
     * musí byť v rámci triedy definovaná ako protected
     * 
     * @return array
     */
    public function accessRules() {
        
        return array(
            
        );
    }
    
    /**
     * 
     */
    public function index( $id ){
        
        Mcore::base()->urlresolver->replaceByOther('user/login/');
    }
}