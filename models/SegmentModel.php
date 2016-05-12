<?php

namespace inhillz\models;

/**
 * Trieda SegmentModel 
 * Objektová reprezentácia úseku s konštatným stúpaním získaná z tréningových dát
 * 
 * @package    inhillz\models
 * @author     Matus Macak <matus.macak@orchidsphere.com>
 * @link       http://ride.inhillz.com/
 * @version    2.0
 * 
 */
class SegmentModel {
    
    /**
     * Začiatočný index segmentu v dátach
     * @var int 
     */
    protected  $index_start;
    
    /**
     * Koncovy index segmentu v dátach
     * @var int 
     */
    protected  $index_end;
    
    /**
     * Dĺžka segmentu
     * @var float 
     */
    protected  $length;
    
    /**
     * Rozdiel v nadmorskej výške medzi začiatočným a koncovým bodom
     * @var float 
     */
    protected  $elevation;
    
    /**
     * Konšturktor segmentu
     * @param type $index_start
     * @param type $index_end
     * @param type $length
     * @param type $elevation
     */
    public function __construct( $index_start, $index_end, $length, $elevation ) {
        
        $this->index_start = $index_start;
        $this->index_end   = $index_end;
        $this->length      = $length;
        $this->elevation   = $elevation;
    }
    
    /**
     * Vypočíta sklon stúpania pre segment
     * @return float
     */
    public function grade(){        
        return $this->elevation / $this->length;
    }
    
    /**
     * Getter pre začiatočný index segmentu
     * @return int
     */
    public function get_index_start() {
        return $this->index_start;
    }

    /**
     * Getter pre koncový index segmentu
     * @return int
     */
    public function get_index_end() {
        return $this->index_end;
    }

    /**
     * Getter pre dĺžku segmentu
     * @return float
     */
    public function get_length() {
        return $this->length;
    }

    /**
     * Getter pre nastúpané metre segmentu
     * @return float
     */
    public function get_elevation() {
        return $this->elevation;
    }


} 