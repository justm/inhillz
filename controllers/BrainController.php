<?php

namespace inhillz\controllers;

use inhillz\components\Analyzer;
use inhillz\components\ann\NeuralNet;
use inhillz\models\ActivityModel;
use inhillz\models\WorkoutModel;

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
     * Uloží tréningy absolvovane s meračom výkonu
     */
    public function getReady(){
        
        $analyzer   = new Analyzer();
        $activities = WorkoutModel::model()->findAll("`date` > '2016-02-03' AND id_user = 1 AND id_activity = 1 AND avg_watts IS NOT NULL");
        
        foreach($activities as $activity){
            $data = $analyzer->analyze($activity);
            ActivityModel::saveRecord($data, $activity->id);
        }
    }
    
    /**
     * Trénovanie neurónovej siete
     */
    public function learn(){
        
        $analyzer   = new Analyzer();
        $neural_net = new NeuralNet();
        $activities = WorkoutModel::model()->findAll("data_file IS NOT NULL AND avg_watts IS NOT NULL AND ascent > 1000 LIMIT 5");
        $train_data = [];
        
        foreach($activities as $activity){
            $analyzer->load($activity);
            $analyzer->detectClimbs();
            
            foreach($analyzer->getSegments() as $segment){
                $power = $analyzer->averagePower($segment);
                
                if($power > 0){
                    $train_data[] = [
                        'inputs'  => $analyzer->segmentData($segment),
                        'outputs' => [$power]
                    ];
                }
            }
        }
        
        echo '<pre>';
        $neural_net->train($train_data);
        echo '</pre>';
    }
}
