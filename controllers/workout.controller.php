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
            '@' => array( 'manual', 'files' ),
        );
    }
    
    /**
     * Manuálne zadanie absolvovaného tréningu
     */
    protected function manual( $id = 0 ){
        
        $activities = Mcore::base()->db->queryPairs( "SELECT `id`, `name` FROM `activity`", 'id', 'name');
        if( ( $workout = WorkoutModel::model()->findById( $id ) ) == NULL ){
            $workout = new WorkoutModel();
        }
        
        if( isset( $_POST['WorkoutModel'] ) ){
            $workout->setAttributes( $_POST['WorkoutModel'] );
            $workout->id_user = Mcore::base()->authenticate->getUserID();
            
            if( $workout->save( TRUE ) ){ 
                Mcore::base()->userflash->setFlash( 'workout-saved', 'alert alert-success', Mcore::t('Your workout has been succesfully saved') );
            }
        }
        $this->render('manual', array( 'workout' => $workout, 'activities' => $activities ) );
    }
    
    /**
     * Upload FIT file
     */
    protected function files(){
        
        if( !empty($_FILES) ){
            $error = array();
            
            foreach($_FILES as $d => $file){
                $user_id = Mcore::base()->authenticate->getUserID();
                
                $outputname   = $user_id . '_' . Helper::getHash($file['name']) . '.csv';
                $file['name'] = $user_id . '_' . $file['name'];
                $inputpath    = Helper::uploadFile($file, 'activities_raw', array('fit'), $error);
                
                //** Do the conversion
                exec(
                    'java -jar ' . MCORE_APP_PATH . 'libraries/FitCSVTool.jar -b '.
                    "{$inputpath} " .
                    MCORE_PROJECT_PATH . "uploads/activities_data/{$outputname}"
                );
            }
        }
        else{
            $this->render('files', array() );
        }
    }
}