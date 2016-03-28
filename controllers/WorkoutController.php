<?php

namespace inhillz\controllers;

use inhillz\components\Analyzer;
use inhillz\components\Helper;
use inhillz\models\WorkoutModel;
use orchidphp\Orchid;

/**
 * Súbor obsahuje ovládač WorkoutController
 * 
 * @package    inhillz\controllers
 * @author     Matus Macak <matus.macak@orchidsphere.com>
 * @link       http://ride.inhillz.com/
 * @version    2.0
 * 
 */
class WorkoutController extends \orchidphp\AbstractController{
    
    /**
     * @inheritdoc
     */
    public function accessRules() {
        
        return array(
            '@' => array( 'update', 'view' ),
        );
    }
        
    /**
     * Úprava existujúceho záznamu o trénigu
     */
    protected function update() {
                
        if( isset($_POST['WorkoutModel']) && isset($_POST['ajaxForm'])){
            
            $ids_w    = implode( ',', $_POST['WorkoutModel']['id'] );
            $id_user  = Orchid::base()->authenticate->getUserID();
            $workouts = WorkoutModel::model()->findAll("id IN ({$ids_w}) AND id_user = {$id_user}", 't.*', '', 'id');
                        
            for ($it = 0; $it < count($workouts); $it++ ){
                
                $w_id = $_POST['WorkoutModel']['id'][$it];
                
                if( empty($workouts[$w_id]) ){
                    continue;
                }
                $vals = array(
                    'id_activity' => $_POST['WorkoutModel']['id_activity'][$it],
                    'title'       => $_POST['WorkoutModel']['title'][$it],
                    'description' => $_POST['WorkoutModel']['description'][$it],
                );
                $workouts[$w_id]->setAttributes($vals);
                
                if( $workouts[$w_id]->save(TRUE) ){
                    Helper::echoAlert('Succesfully saved', 'alert alert-success');
                }
            }
        }
    }
    
    /**
     * Detailné zobrazenie tréningu
     * @param int $id
     */
    public function view( $id = 0 ){
        
	$workout_summary = WorkoutModel::model()->findById(intval($id));

        if( empty($workout_summary) ){
            PageController::controller()->error(404);
        }
                       
        $analyzer = new Analyzer();
        $workout_data = empty($workout_summary->data_file)? [] : $analyzer->analyze($workout_summary);
        
        $this->render('view', array(
            'workout_summary' => $workout_summary,
            'workout_data' => $workout_data)
        );
    }    
}
