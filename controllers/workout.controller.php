<?php
/**
 * Súbor obsahuje triedu WorkoutController
 *
 * @author Matus Macak < matus.macak@folcon.sk > 
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
            '@' => array( 'update', 'view' ),
        );
    }
        
    /**
     * Úprava existujúceho záznamu o trénigu
     */
    protected function update() {
                
        if( isset($_POST['WorkoutModel']) && isset($_POST['ajaxForm'])){
            
            $ids_w    = implode( ',', $_POST['WorkoutModel']['id'] );
            $id_user  = Mcore::base()->authenticate->getUserID();
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
        
        $workout_summary = WorkoutModel::model()->findById($id);

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