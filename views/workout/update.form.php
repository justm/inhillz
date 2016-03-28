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
 * @var WorkoutModel $data->model 
 * @var array $data->activities
 */
?>
<div class="row">
    <div class="col-xs-12 col-md-6">
        <div class="row">
            <div class="form-group col-xs-12">
                <?php echo MhtmlCore::mLabel( $data->model, 'id_activity'); ?>
                <?php echo MhtmlCore::mSelect( $data->model, 'id_activity', $data->activities, array( 'class' => 'form-control'), TRUE ); ?>
                <?php echo MhtmlCore::mHiddenInput($data->model, 'id', array(), TRUE); ?>
            </div>
            <div class="form-group col-xs-12">
                <?php echo MhtmlCore::mLabel( $data->model, 'title'); ?>
                <?php echo MhtmlCore::mTextInput( $data->model, 'title', array('class' => 'form-control'), TRUE); ?>
            </div>
            <div class="form-group col-xs-12">
                <?php echo MhtmlCore::mLabel( $data->model, 'description'); ?>
                <?php echo MhtmlCore::mTextarea( $data->model, 'description', array('rows' => 4 ,'class' => 'form-control'), TRUE); ?>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-md-6">
        <?php //Mcore::var_dump($data); ?>
    </div>
    <div class="clearfix"></div>
    <hr/>
</div>