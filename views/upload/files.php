<?php

use orchidphp\HTMLhelper;
use orchidphp\Orchid;

/**
 * View pre upload súboru s údajmi o tréningu 
 *
 * @package    inhillz\views
 * @author     Matus Macak <matus.macak@orchidsphere.com>
 * @link       http://ride.inhillz.com/
 * @version    2.0
 * 
 */
?>
<div class="col-xs-12 center-block">
    <?php
        echo HTMLhelper::displayFlash();
        //echo HTMLhelper::displayErrors($data->workout);
    ?>
    <form method="POST" action="<?php echo ENTRY_SCRIPT_URL . 'upload/files/'?>" class="ajaxForm">
        <div class="well">
            <input type="file" multiple="multiple" name="workout_files"/>
            <div class="progress hidden">
                <div class="progress-bar progress-bar-info progress-bar-striped" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="min-width: 2em;">0%</div>
            </div>
        </div>      
        <p><?php echo Orchid::t('Works for multiple .fit files 25MB or smaller. Choose up to 25 files.')?></p>
    </form>
</div>

    
    