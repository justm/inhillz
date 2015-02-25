<?php
/**
 * View pre manuálne zadanie absolvovaného tréningu
 *
 * @author Matus Macak < matus.macak@folcon.sk > 
 * @link http://www.folcon.sk/
 * @version 2.0
 * @since Subor je súčasťou aplikácie od verzie 2.0
 * @package controllers
 * 
 * @var WorkoutModel $data->workout 
 * @var array $data->activities
 */
    echo MhtmlCore::beginForm( 
            ENTRY_SCRIPT_URL . 'workout/update/', 
            'POST', 
            array('role' => 'form', 'class'=>'ajaxForm' ) 
        ); 
    


    
    