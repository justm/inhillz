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
<div class="col-xs-12 center-block">
    <?php
        echo MhtmlCore::displayFlash();
        //echo MhtmlCore::displayErrors($data->workout);
    ?>
    <form method="POST" action="<?php echo ENTRY_SCRIPT_URL . 'workout/files/'?>">
        <div class="well">
            <input type="file" multiple="multiple" name="workout_files"/>
            <div class="progress hidden">
                <div class="progress-bar progress-bar-info progress-bar-striped" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="min-width: 2em;">0%</div>
            </div>
        </div>        
    </form>
    <p><?php echo Mcore::t('Works for multiple .fit files 25MB or smaller. Choose up to 25 files.')?></p>
</div>

    
    