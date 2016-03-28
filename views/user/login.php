<?php

use inhillz\models\UserModel;
use orchidphp\HTMLhelper;
use orchidphp\Orchid;

/**
 * Náhľad pre zobrazenie formuláru na prihlásenie do systému
 *
 * @package    inhillz\views
 * @author     Matus Macak <matus.macak@orchidsphere.com>
 * @link       http://ride.inhillz.com/
 * @version    2.0
 * 
 * @var UserModel $data->user 
 */
?>
<div class="row">
    <div class="col-xs-12 col-sm-10 col-md-8 col-lg-6 center-block">
        <?php
            echo HTMLhelper::displayFlash();
            echo HTMLhelper::displayErrors($data->user);
        ?>
        <div class="row">
        <?php echo HTMLhelper::beginForm('', 'POST', array('role' => 'form' ) ); ?>
            <div class="form-group col-xs-12">
                <?php echo HTMLhelper::mLabel( $data->user, 'email'); ?>
                <?php echo HTMLhelper::mTextInput( $data->user, 'email', array( 'class' => 'form-control' ) ); ?>
            </div>
            <div class="form-group col-xs-12">
                <?php echo HTMLhelper::mLabel( $data->user, 'password'); ?>
                <?php echo HTMLhelper::mPasswordInput( $data->user, 'password', array('class' => 'form-control')); ?>
            </div>
            <div class="clearfix"></div>
            <div class="form-group  col-xs-12 col-sm-6 center-block ajaxForm-submit">
                <button type="submit" class="btn btn-lg btn-info btn-block"><?php echo Orchid::t('Log in'); ?></button>
            </div>
        <?php echo HTMLhelper::endForm(); ?>
        </div>
    </div>
</div>

    
    