<?php
/**
 * Súbor obsahuje triedu UploadController
 *
 * @author Matus Macak < matus.macak@folcon.sk > 
 * @link http://www.folcon.sk/
 * @version 2.0
 * @since Subor je súčasťou aplikácie od verzie 2.0
 * @package controllers
 * 
 */

/**
 * Ovládač UploadController 
 */
class UploadController extends McontrollerCore{
    
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
            $workout->start_time = time() - 3600;
        }
        
        if( isset( $_POST['WorkoutModel'] ) ){
            $workout->setAttributes( $_POST['WorkoutModel'] );
            
            $workout->id_user = Mcore::base()->authenticate->getUserID();
            $workout->start_time = strtotime( $_POST['WorkoutModel']['date'] . ' ' . $_POST['WorkoutModel']['start_time'] . ':00');
            
            $t_time = explode(':', $_POST['WorkoutModel']['duration']);
            $workout->total_timer_time = $workout->total_elapsed_time = $t_time[0] * 3600 + $t_time[1] * 60 + $t_time[2];
            
            if( $workout->save( TRUE ) ){ 
                Mcore::base()->userflash->setFlash( 'workout-saved', 'alert alert-success', Mcore::t('Your workout has been succesfully saved') );
            }
        }
        
        $workout->start_time = date('H:i', $workout->start_time);

        $this->render('manual', array( 'workout' => $workout, 'activities' => $activities ) );
    }
    
    /**
     * Upload and process FIT file
     */
    protected function files(){
        
        if( !empty($_FILES) ){
            $activities   = Mcore::base()->db->queryPairs( "SELECT `id`, `name` FROM `activity`", 'id', 'name');
            $success      = 0;
                        
            foreach($_FILES as $d => $file){
                $error   = array();
                $user_id = Mcore::base()->authenticate->getUserID();
                
                $outputname   = $user_id . '_' . Helper::getHash($file['name']) . '.csv';
                $file['name'] = $user_id . '_' . $file['name'];
                $inputpath    = Helper::uploadFile($file, 'activities_raw', array('fit'), $error);
                $outputpath   = MCORE_PROJECT_PATH . "uploads/activities_data/{$outputname}";
                
                if( !empty($error) ){
                    Helper::echoAlert( implode('<br/>', $error), 'alert-danger');
                    continue;
                }
                else{
                    $success = 1;
                }
                
                //** Do the conversion
                exec(
                    'java -jar ' . MCORE_APP_PATH . 'libraries/FitCSVTool.jar -b '.
                    "{$inputpath} {$outputpath}"
                );
                    
                //** Read CSV file and get totals
                $data_model   = new Csv_activity_model($outputpath);
                $session_data = $data_model->getSessionData();
                $data_units   = $data_model->getUnits();  
                
                //** Create DB entry
                $w_model = new WorkoutModel();
                
                $w_model->id_user = $user_id;
                $w_model->id_activity = 1;
                $w_model->start_time = $session_data->start_time + EPOCH_TIMESTAMP_OFFSET;
                $w_model->title = date("Y/m/d", $w_model->start_time) . ' Activity';
                
                $w_model->raw_file = $file['name'];
                $w_model->data_file = $outputname;
                
                $w_model->avg_hr = $session_data->avg_heart_rate;
                $w_model->max_hr = $session_data->max_heart_rate;
                $w_model->distance = Helper::convertUnits($session_data->total_distance, $data_units->distance, 'km');
                $w_model->avg_speed = Helper::convertUnits($session_data->avg_speed, $data_units->speed, 'km/h');
                $w_model->ascent = $session_data->total_ascent;
                $w_model->avg_watts = $session_data->avg_power;
                $w_model->total_timer_time = $session_data->total_timer_time;
                $w_model->total_elapsed_time = $session_data->total_elapsed_time;
                                
                //** Duration and Date might be deprecated since 2.0
                $w_model->duration = date("H:i:s",$session_data->total_timer_time);
                $w_model->date = date("Y-m-d", $w_model->start_time);
                
               if( $w_model->save(TRUE) ){
                    $this->renderPartial('update.form', array('model' => $w_model, 'activities' => $activities), 'workout');
               }
                
            }
            
            if($success){
                $this->renderPartial('update.submit', array(), 'workout');
            }
            else{
                header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request', true, 400);
            }
        }
        else{
            $this->render('files', array() );
        }
    }
}