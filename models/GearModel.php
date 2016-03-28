<?php

namespace inhillz\models;

/**
 * Trieda GearModel, 
 * Model pre DB tabuľku GEAR, ktorá uchováva údaje o vybavení športovcov
 * 
 * @package    inhillz\models
 * @author     Matus Macak <matus.macak@orchidsphere.com>
 * @link       http://ride.inhillz.com/
 * @version    2.0
 * 
 * @property int $id
 * @property string $name
 * @property float $weight 
 * @property float $cda_coef 
 * @property float $crr_coef
 * @property int $id_user
 */
class GearModel extends \orchidphp\AbstractModel{
    
    /**
     * @inheritdoc
     */
    public static function model( $className = __CLASS__ ){
        return parent::model( $className );
    }
    
    /**
     * @inheridoc
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

