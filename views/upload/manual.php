<?php
/**
 * View pre manuálne zadanie absolvovaného tréningu
 *
 * @author Matus Macak < matus.macak@folcon.sk > 
 * @link http://www.folcon.sk/
 * @version 2.0
 * @since Subor je súčasťou aplikácie od verzie 2.0
 * @package controllers
 * 
 * @var WorkoutModel $data->workout 
 * @var array $data->activities
 */
?>
<div class="col-xs-12 col-sm-10 col-md-8 col-lg-6 center-block">
    <?php
        echo MhtmlCore::displayFlash();
        echo MhtmlCore::displayErrors($data->workout);
    ?>
    <div class="row">
    <?php echo MhtmlCore::beginForm('', 'POST', array('role' => 'form' ) ); ?>
        <div class="form-group col-xs-12">
            <?php echo MhtmlCore::mLabel( $data->workout, 'title'); ?>
            <?php echo MhtmlCore::mTextInput( $data->workout, 'title', array('class' => 'form-control')); ?>
        </div>
        <div class="form-group col-xs-12">
            <?php echo MhtmlCore::mLabel( $data->workout, 'description'); ?>
            <?php echo MhtmlCore::mTextarea( $data->workout, 'description', array('rows' => 4 ,'class' => 'form-control')); ?>
        </div>
        <div class="form-group col-xs-12 col-sm-6">
            <?php echo MhtmlCore::mLabel( $data->workout, 'id_activity'); ?>
            <?php echo MhtmlCore::mSelect( $data->workout, 'id_activity', $data->activities, array( 'class' => 'form-control' ) ); ?>
        </div>
        <div class="form-group col-xs-12 col-sm-6">
            <?php echo MhtmlCore::mLabel( $data->workout, 'duration'); ?>
            <?php echo MhtmlCore::mTextInput( $data->workout, 'duration', array('class' => 'form-control')); ?>
        </div>
        <div class="form-group col-xs-12 col-sm-6">
            <?php echo MhtmlCore::mLabel( $data->workout, 'avg_hr'); ?>
            <?php echo MhtmlCore::mTextInput( $data->workout, 'avg_hr', array('class' => 'form-control')); ?>
        </div>
        <div class="form-group col-xs-12 col-sm-6">
            <?php echo MhtmlCore::mLabel( $data->workout, 'max_hr'); ?>
            <?php echo MhtmlCore::mTextInput( $data->workout, 'max_hr', array('class' => 'form-control')); ?>
        </div>
        <div class="form-group col-xs-12 col-sm-6">
            <?php echo MhtmlCore::mLabel( $data->workout, 'distance'); ?>
            <?php echo MhtmlCore::mTextInput( $data->workout, 'distance', array('class' => 'form-control')); ?>
        </div>
        <div class="form-group col-xs-12 col-sm-6">
            <?php echo MhtmlCore::mLabel( $data->workout, 'avg_speed'); ?>
            <?php echo MhtmlCore::mTextInput( $data->workout, 'avg_speed', array('class' => 'form-control')); ?>
        </div>
        <div class="form-group col-xs-12 col-sm-6">
            <?php echo MhtmlCore::mLabel( $data->workout, 'date'); ?>
            <?php echo MhtmlCore::mTextInput( $data->workout, 'date', array('class' => 'form-control')); ?>
        </div>
        <div class="form-group col-xs-12 col-sm-6">
            <?php echo MhtmlCore::mLabel( $data->workout, 'start_time'); ?>
            <?php echo MhtmlCore::mTextInput( $data->workout, 'start_time', array('class' => 'form-control')); ?>
        </div>
        <div class="clearfix"></div>
        <div class="form-group  col-xs-12 col-sm-6 center-block ajaxForm-submit">
            <button type="submit" class="btn btn-lg btn-info btn-block"><?php echo Mcore::t('Save'); ?></button>
        </div>
    <?php echo MhtmlCore::endForm(); ?>
    </div>
</div>

    
    