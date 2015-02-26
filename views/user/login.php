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
<div class="row">
    <div class="col-xs-12 col-sm-10 col-md-8 col-lg-6 center-block">
        <?php
            echo MhtmlCore::displayFlash();
            echo MhtmlCore::displayErrors($data->user);
        ?>
        <div class="row">
        <?php echo MhtmlCore::beginForm('', 'POST', array('role' => 'form' ) ); ?>
            <div class="form-group col-xs-12">
                <?php echo MhtmlCore::mLabel( $data->user, 'email'); ?>
                <?php echo MhtmlCore::mTextInput( $data->user, 'email', array( 'class' => 'form-control' ) ); ?>
            </div>
            <div class="form-group col-xs-12">
                <?php echo MhtmlCore::mLabel( $data->user, 'password'); ?>
                <?php echo MhtmlCore::mPasswordInput( $data->user, 'password', array('class' => 'form-control')); ?>
            </div>
            <div class="clearfix"></div>
            <div class="form-group  col-xs-12 col-sm-6 center-block ajaxForm-submit">
                <button type="submit" class="btn btn-lg btn-info btn-block"><?php echo Mcore::t('Log in'); ?></button>
            </div>
        <?php echo MhtmlCore::endForm(); ?>
        </div>
    </div>
</div>

    
    