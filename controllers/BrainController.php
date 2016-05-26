<?php

namespace inhillz\controllers;

use inhillz\components\ann\NeuralNet;

/**
 * SupervisorController má na starosti trénovanie neurónovej siete
 * 
 * @package    inhillz\controllers
 * @author     Matus Macak <matus.macak@orchidsphere.com>
 * @link       http://ride.inhillz.com/
 * @version    2.0
 */
class BrainController extends AbstractWebController{
    
    /**
     * Trénovanie neurónovej siete
     */
    public function learn(){
        
        ini_set('memory_limit', '2048M');
        
        require PROJECT_PATH . 'components/ann/train.data.php';
        echo '<pre>';
        shuffle($train_data);
        $net = new NeuralNet();
        $net->train($train_data);
        echo '</pre>';
    }
}