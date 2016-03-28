<?php

use inhillz\models\WorkoutModel;
use orchidphp\HTMLhelper;

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
    <div class="col-xs-12 col-md-6">
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
                <?php echo HTMLhelper::mTextarea( $data->model, 'description', array('rows' => 4 ,'class' => 'form-control'), TRUE); ?>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-md-6">
        <?php //Orchid::var_dump($data); ?>
    </div>
    <div class="clearfix"></div>
    <hr/>
</div>