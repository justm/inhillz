<?php
/**
 * Súbor obsahuje triedu WorkoutModel, 
 *
 * @author Matus Macak < matus.macak@folcon.sk > 
 * @link http://www.folcon.sk/
 * @version 2.0
 * @since Subor je súčasťou aplikácie od verzie 2.0
 * @package models
 * 
 */

/**
 * Trieda WorkoutModel
 * 
 * @property int $id 
 * @property String $duration
 * @property float total_timer_time
 * @property float total_elapsed_time
 * @property String title
 * @property int $description
 * @property int $feelings
 * @property int $min_hr
 * @property int $avg_hr
 * @property int $max_hr
 * @property int $distance
 * @property int $avg_speed
 * @property int $ascent
 * @property int $max_altitude
 * @property int $avg_watts
 * @property int $max_watts
 * @property String $avg_pace
 * @property String $date
 * @property int $id_activity
 * @property int $id_user
 * @property int $id_visibility
 * @property int $start_time
 * @property int $id_label

 * 
 */
class WorkoutModel extends MmodelCore{
        
    /**
     * @inheritdoc
     */
    public static function model( $className = __CLASS__ ){
        return parent::model( $className );
    }
    
    /**
     * @inheritdoc
     */
    public function table(){
        return 'training_entry';
    }
    
    /** 
     * @inheritdoc
     */
    public function labels() {
        
        return array(
            'duration'      => Mcore::t('Duration','workout'),
            'description'   => Mcore::t('Description','workout'),
            'feelings'      => Mcore::t('Feelings','workout'),
            'min_hr'        => Mcore::t('Min HR','workout'),
            'avg_hr'        => Mcore::t('Avg HR','workout'),
            'max_hr'        => Mcore::t('Max HR','workout'),
            'distance'      => Mcore::t('Distance','workout'),
            'avg_speed'     => Mcore::t('Avg Speed','workout'),
            'ascent'        => Mcore::t('Ascent','workout'),
            'max_altitude'  => Mcore::t('Max Altitude','workout'),
            'avg_watts'     => Mcore::t('Avg Watts','workout'),
            'max_watts'     => Mcore::t('Max Watts','workout'),
            'avg_pace'      => Mcore::t('Avg Pace','workout'),
            'date'          => Mcore::t('Date','workout'),
            'id_activity'   => Mcore::t('Activity','workout'),
            'id_visibility' => Mcore::t('Visibility','workout'),
            'id_label'      => Mcore::t('Label','workout'),

        );
    }
    
    /** 
     * @inheritdoc
     */
    public function tags( ) {
        
        $this->tags = array(
            'duration' => array(
                    'label'       => Mcore::t('Duration','workout'),
                    'placeholder' => Mcore::t('HH:MM:SS','workout'),
                ),
            'title' => array(
                    'label'       => Mcore::t('Activity name','workout'),
                    'placeholder' => Mcore::t('Name your activity','workout'),
                ),
            'description' => array(
                    'label'       => Mcore::t('Description','workout'),
                    'placeholder' => Mcore::t('How did it go?')
                ),
            'feelings' => array(
                    'label'       => Mcore::t('Feelings','workout'),
                ),
            'avg_hr' => array(
                    'label'       => Mcore::t('AVG HR','workout'),
                    'placeholder' => Mcore::t('Average Heart Rate','workout'),
                ),
            'max_hr' => array(
                    'label'       => Mcore::t('MAX HR','workout'),
                    'placeholder' => Mcore::t('Maximum Heart Rate','workout'),
                ),
            'distance' => array(
                    'label'       => Mcore::t('Distance','workout'),
                    'placeholder' => Mcore::t('Total Distance in kilometers','workout'),
                ),
            'avg_speed' => array(
                    'label'       => Mcore::t('AVG Speed','workout'),
                    'placeholder' => Mcore::t('Average Speed (km/h)','workout'),
                ),
            'avg_pace' => array(
                    'label'       => Mcore::t('AVG Pace','workout'),
                    'placeholder' => Mcore::t('Average Pace (mm:ss/km)','workout'),
                ),
            'ascent' => array(
                    'label'       => Mcore::t('Ascent','workout'),
                    'placeholder' => Mcore::t('Total meters climbed','workout'),
                ),
            'avg_watts' => array(
                    'label'       => Mcore::t('AVG Watts','workout'),
                    'placeholder' => Mcore::t('Average Watts','workout'),
                ),
            'max_watts' => array(
                    'label'       => Mcore::t('MAX Watts','workout'),
                    'placeholder' => Mcore::t('Maximum Watts','workout'),
                ),
            'date' => array(
                    'label'       => Mcore::t('Date','workout'),
                    'placeholder' => Mcore::t('YYYY-MM-DD','workout'),
                ),
            'start_time' => array(
                    'label'       => Mcore::t('Time','workout'),
                    'placeholder' => Mcore::t('HH:MM','workout'),
                ),
            'id_activity' => array(
                    'label'       => Mcore::t('Activity','workout'),
                ),
            'id_visibility' => array(
                    'label'       => Mcore::t('Visibility','workout'),
                ),
            'id_label' => array(
                    'label'       => Mcore::t('Label','workout'),
                ),
        );
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        
        return array(
            'required' => ' ',
            'length' => array(),
        );
    }
    
    /**
     * @inheritdoc
     */
    protected function afterFind() {
        $this->start_time         = date('H:i', strtotime($this->start_time) );
        $this->date               = date('l, F d, Y', strtotime($this->date) );
        $this->total_elapsed_time = date("H:i:s", strtotime($this->total_elapsed_time) );
    }
} 