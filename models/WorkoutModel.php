<?php

namespace inhillz\models;

use orchidphp\Orchid;

/**
 * Trieda WorkoutModel
 *
 * @package    inhillz\models
 * @author     Matus Macak <matus.macak@orchidsphere.com>
 * @link       http://ride.inhillz.com/
 * @version    2.0
 * 
 * @property int $id 
 * @property string $duration
 * @property float total_timer_time
 * @property float total_elapsed_time
 * @property string title
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
 * @property string $avg_pace
 * @property string $date
 * @property int $id_activity
 * @property int $id_user
 * @property int $id_visibility
 * @property int $start_time
 * @property int $id_label
 * 
 */
class WorkoutModel extends \orchidphp\AbstractModel{
        
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
            'duration'      => Orchid::t('Duration','workout'),
            'description'   => Orchid::t('Description','workout'),
            'feelings'      => Orchid::t('Feelings','workout'),
            'min_hr'        => Orchid::t('Min HR','workout'),
            'avg_hr'        => Orchid::t('Avg HR','workout'),
            'max_hr'        => Orchid::t('Max HR','workout'),
            'distance'      => Orchid::t('Distance','workout'),
            'avg_speed'     => Orchid::t('Avg Speed','workout'),
            'ascent'        => Orchid::t('Ascent','workout'),
            'max_altitude'  => Orchid::t('Max Altitude','workout'),
            'avg_watts'     => Orchid::t('Avg Watts','workout'),
            'max_watts'     => Orchid::t('Max Watts','workout'),
            'avg_pace'      => Orchid::t('Avg Pace','workout'),
            'date'          => Orchid::t('Date','workout'),
            'id_activity'   => Orchid::t('Activity','workout'),
            'id_visibility' => Orchid::t('Visibility','workout'),
            'id_label'      => Orchid::t('Label','workout'),
        );
    }
    
    /** 
     * @inheritdoc
     */
    public function tags( ) {
        
        $this->tags = array(
            'duration' => array(
                    'label'       => Orchid::t('Duration','workout'),
                    'placeholder' => Orchid::t('HH:MM:SS','workout'),
                ),
            'title' => array(
                    'label'       => Orchid::t('Activity name','workout'),
                    'placeholder' => Orchid::t('Name your activity','workout'),
                ),
            'description' => array(
                    'label'       => Orchid::t('Description','workout'),
                    'placeholder' => Orchid::t('How did it go?')
                ),
            'feelings' => array(
                    'label'       => Orchid::t('Feelings','workout'),
                ),
            'avg_hr' => array(
                    'label'       => Orchid::t('AVG HR','workout'),
                    'placeholder' => Orchid::t('Average Heart Rate','workout'),
                ),
            'max_hr' => array(
                    'label'       => Orchid::t('MAX HR','workout'),
                    'placeholder' => Orchid::t('Maximum Heart Rate','workout'),
                ),
            'distance' => array(
                    'label'       => Orchid::t('Distance','workout'),
                    'placeholder' => Orchid::t('Total Distance in kilometers','workout'),
                ),
            'avg_speed' => array(
                    'label'       => Orchid::t('AVG Speed','workout'),
                    'placeholder' => Orchid::t('Average Speed (km/h)','workout'),
                ),
            'avg_pace' => array(
                    'label'       => Orchid::t('AVG Pace','workout'),
                    'placeholder' => Orchid::t('Average Pace (mm:ss/km)','workout'),
                ),
            'ascent' => array(
                    'label'       => Orchid::t('Ascent','workout'),
                    'placeholder' => Orchid::t('Total meters climbed','workout'),
                ),
            'avg_watts' => array(
                    'label'       => Orchid::t('AVG Watts','workout'),
                    'placeholder' => Orchid::t('Average Watts','workout'),
                ),
            'max_watts' => array(
                    'label'       => Orchid::t('MAX Watts','workout'),
                    'placeholder' => Orchid::t('Maximum Watts','workout'),
                ),
            'date' => array(
                    'label'       => Orchid::t('Date','workout'),
                    'placeholder' => Orchid::t('YYYY-MM-DD','workout'),
                ),
            'start_time' => array(
                    'label'       => Orchid::t('Time','workout'),
                    'placeholder' => Orchid::t('HH:MM','workout'),
                ),
            'id_activity' => array(
                    'label'       => Orchid::t('Activity','workout'),
                ),
            'id_visibility' => array(
                    'label'       => Orchid::t('Visibility','workout'),
                ),
            'id_label' => array(
                    'label'       => Orchid::t('Label','workout'),
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
        $this->start_time         = date('H:i', $this->start_time);
        $this->total_elapsed_time = date("H:i:s", strtotime($this->total_elapsed_time));
    }
} 
