<?php

use inhillz\components\Helper;
use inhillz\models\WorkoutModel;
use orchidphp\HTMLhelper;
use orchidphp\Orchid;

/**
 * View pre manuálne zadanie absolvovaného tréningu
 *
 * @package    inhillz\views
 * @author     Matus Macak <matus.macak@orchidsphere.com>
 * @link       http://ride.inhillz.com/
 * @version    2.0
 * 
 * @var WorkoutModel $data->model 
 * @var array $data->activities
 */
?>
<div class="row">
    <div class="col-xs-12 col-sm-6">
        <div class="row">
            <div class="form-group col-xs-12">
                <?php echo HTMLhelper::mLabel( $data->model, 'id_activity'); ?>
                <?php echo HTMLhelper::mSelect( $data->model, 'id_activity', $data->activities, array( 'class' => 'form-control'), TRUE ); ?>
                <?php echo HTMLhelper::mHiddenInput($data->model, 'id', array(), TRUE); ?>
            </div>
            <div class="form-group col-xs-12">
                <?php echo HTMLhelper::mLabel( $data->model, 'title'); ?>
                <?php echo HTMLhelper::mTextInput( $data->model, 'title', array('class' => 'form-control'), TRUE); ?>
            </div>
            <div class="form-group col-xs-12">
                <?php echo HTMLhelper::mLabel( $data->model, 'description'); ?>
                <?php echo HTMLhelper::mTextarea( $data->model, 'description', array('rows' => 3 ,'class' => 'form-control'), TRUE); ?>
            </div>
        </div>
    </div>
    <div class="col-xs-6 col-sm-3">
        <h3><?php echo Orchid::t('Details'); ?></h3><?php 
    
        $sessions = ['date'=>'', 'distance'=>'km', 'duration'=>'', 'ascent' => 'm'];
        
        foreach($sessions as $value => $unit){
            if(isset($data->model->$value)){
                echo '<div class="h4">' . $data->model->$value . $unit . '</div>'; 
            }
        }
        
    ?></div>
    <div class="col-xs-6 col-sm-3">
        <div class="map-canvas map-thumb m-t m-b" id="map-<?php echo $data->model->id?>" style="height: 230px"></div><?php 
        
            $points = Helper::getPositionsFromRecord($data->record);
            
            if(!empty($points)){
                $coordinates = 'new google.maps.LatLng(' . implode('), new google.maps.LatLng(', $points) . ')';
                echo 
                   '<script type="text/javascript">
                        var coordinates = [' . $coordinates . '];
                        map_draw("map-' . $data->model->id .'", coordinates);
                    </script>';
            }
        ?>
    </div>
    <div class="clearfix"></div>
    <hr/>
</div>