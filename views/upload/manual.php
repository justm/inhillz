<?php

use inhillz\models\WorkoutModel;
use orchidphp\HTMLhelper;
use orchidphp\Orchid;

/**
 * Náhľad pre manuálne zadanie absolvovaného tréningu
 *
 * @package    inhillz\views
 * @author     Matus Macak <matus.macak@orchidsphere.com>
 * @link       http://ride.inhillz.com/
 * @version    2.0
 * 
 * @var WorkoutModel $data->workout 
 * @var array $data->activities
 */
?>
<div class="col-xs-12 col-sm-10 col-md-8 col-lg-6 center-block">
    <?php
        echo HTMLhelper::displayFlash();
        echo HTMLhelper::displayErrors($data->workout);
    ?>
    <div class="row">
    <?php echo HTMLhelper::beginForm('', 'POST', array('role' => 'form' ) ); ?>
        <div class="form-group col-xs-12">
            <?php echo HTMLhelper::mLabel( $data->workout, 'title'); ?>
            <?php echo HTMLhelper::mTextInput( $data->workout, 'title', array('class' => 'form-control')); ?>
        </div>
        <div class="form-group col-xs-12">
            <?php echo HTMLhelper::mLabel( $data->workout, 'description'); ?>
            <?php echo HTMLhelper::mTextarea( $data->workout, 'description', array('rows' => 4 ,'class' => 'form-control')); ?>
        </div>
        <div class="form-group col-xs-12 col-sm-6">
            <?php echo HTMLhelper::mLabel( $data->workout, 'id_activity'); ?>
            <?php echo HTMLhelper::mSelect( $data->workout, 'id_activity', $data->activities, array( 'class' => 'form-control' ) ); ?>
        </div>
        <div class="form-group col-xs-12 col-sm-6">
            <?php echo HTMLhelper::mLabel( $data->workout, 'duration'); ?>
            <?php echo HTMLhelper::mTextInput( $data->workout, 'duration', array('class' => 'form-control')); ?>
        </div>
        <div class="form-group col-xs-12 col-sm-6">
            <?php echo HTMLhelper::mLabel( $data->workout, 'avg_hr'); ?>
            <?php echo HTMLhelper::mTextInput( $data->workout, 'avg_hr', array('class' => 'form-control')); ?>
        </div>
        <div class="form-group col-xs-12 col-sm-6">
            <?php echo HTMLhelper::mLabel( $data->workout, 'max_hr'); ?>
            <?php echo HTMLhelper::mTextInput( $data->workout, 'max_hr', array('class' => 'form-control')); ?>
        </div>
        <div class="form-group col-xs-12 col-sm-6">
            <?php echo HTMLhelper::mLabel( $data->workout, 'distance'); ?>
            <?php echo HTMLhelper::mTextInput( $data->workout, 'distance', array('class' => 'form-control')); ?>
        </div>
        <div class="form-group col-xs-12 col-sm-6">
            <?php echo HTMLhelper::mLabel( $data->workout, 'avg_speed'); ?>
            <?php echo HTMLhelper::mTextInput( $data->workout, 'avg_speed', array('class' => 'form-control')); ?>
        </div>
        <div class="form-group col-xs-12 col-sm-6">
            <?php echo HTMLhelper::mLabel( $data->workout, 'date'); ?>
            <?php echo HTMLhelper::mTextInput( $data->workout, 'date', array('class' => 'form-control')); ?>
        </div>
        <div class="form-group col-xs-12 col-sm-6">
            <?php echo HTMLhelper::mLabel( $data->workout, 'start_time'); ?>
            <?php echo HTMLhelper::mTextInput( $data->workout, 'start_time', array('class' => 'form-control')); ?>
        </div>
        <div class="clearfix"></div>
        <div class="form-group  col-xs-12 col-sm-6 center-block ajaxForm-submit">
            <button type="submit" class="btn btn-lg btn-info btn-block"><?php echo Orchid::t('Save'); ?></button>
        </div>
    <?php echo HTMLhelper::endForm(); ?>
    </div>
</div>

    
    