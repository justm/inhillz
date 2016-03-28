<?php
/**
 * Súbor obsahuje triedu GearModel, ktorá predstavuje model pre DB tabuľky GEAR
 *
 * @author Matus Macak < matus.macak@folcon.sk > 
 * @link http://www.folcon.sk/
 * @version 1.1 Mercury drop
 * @since Subor je súčasťou aplikácie od verzie 1.0
 * @package models
 * 
 */

/**
 * Trieda UserModel predstavuje model pre DB tabuľku USER, ktorá uchováva údaje o 
 * registrovaných používateľoch
 *  @property int $id
 *  @property String $name
 *  @property Float $weight 
 *  @property Float $cda_coef 
 *  @property Float $crr_coef
 *  @property int $id_user
 */

class GearModel extends MmodelCore{
    
    /**
     * @inheritdoc
     */
    public static function model( $className = __CLASS__ ){
        return parent::model( $className );
    }
    
    /**
     * Metóda vráti názov tabuľky v DB, ku ktorej prislúcha tento model
     * @return String Názov tabuľky
     */
    public function table(){
        return 'gear';
    }
    
    /** 
     * @inheritdoc
     */
    public function labels() {
        
        return array();
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        
        return array();
    }
} 

