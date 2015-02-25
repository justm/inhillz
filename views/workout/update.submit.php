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
<div class="clearfix"></div>
<div class="col-xs-12">
    <div class="form-group col-xs-12 col-sm-6 center-block">
        <button type="submit" class="btn btn-lg btn-info btn-block" data-loading-text="Processing..."><?php echo Mcore::t('Save & View'); ?></button>
    </div>
    <?php echo MhtmlCore::endForm();?>
</div>


    
    