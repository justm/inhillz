<?php
/**
 * Súbor obsahuje triedu WorkoutController
 *
 * @author Matus Macak < matus.macak@folcon.sk > 
 * @link http://www.folcon.sk/
 * @version 2.0
 * @since Subor je súčasťou aplikácie od verzie 2.0
 * @package controllers
 * 
 */

/**
 * Ovládač WorkoutController 
 */
class WorkoutController extends McontrollerCore{
    
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
     * Prihlásenie používateľa
     */
    protected function update() {
        
        if( isset($_POST['WorkoutModel']) && isset($_POST['ajaxForm'])){
            Mcore::var_dump($_POST);
        }
    }
}