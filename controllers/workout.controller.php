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
    protected function view( $id = 0 ){
        
        $workout_basics = WorkoutModel::model()->findById(intval($id));

        if( empty($workout_basics) ){
            PageController::controller()->error(404);
        }
                       
        /** @todo Check if $workout->data_file not empty, e.g. manual entry */
        
        /** this is TEMP, will be read from BigTable */ 
        $data_model = new Csv_activity_model(MCORE_PROJECT_PATH . 'uploads/activities_data/' . $workout_basics->data_file);
        $workout_data = $data_model->getRecordData();
        /** end of TEMP */
        
        $this->render('view', array(
            'workout_basics' => $workout_basics,
            'workout_data' => $workout_data)
        );
    }
}