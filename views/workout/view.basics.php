<?php
/**
 * Základné informácie - Časť náhľadu pre detailné zobrazenie tréningu
 *
 * @author Matus Macak < matus.macak@folcon.sk > 
 * @version 2.0
 * @since Subor je súčasťou aplikácie od verzie 2.0
 * @package views.workout
 * 
 * @var WorkoutModel $data->workout_summary
 * @var Array $data->workout_data
 */
?><div class="col-xs-12 col-md-5">
    <div class="panel panel-info summary" style="min-height: 400px">
        <div class="panel-heading"><h1 class="h5"><?php echo $data->workout_summary->title; ?></h1></div>
        <div class="panel-body">
            <div class="row">
                <div class="summary-inline summary-lg">
                    <?php echo $data->workout_summary->duration; ?>
                    <label class="summary-label"><?php echo Mcore::t('Moving Time'); ?></label>
                </div>
                <div class="summary-inline summary-lg">
                    <?php echo number_format( $data->workout_summary->distance, 1 ); ?><abbr class="unit" title="kilometers">km</abbr>
                    <label class="summary-label"><?php echo Mcore::t('Distance'); ?></label>
                </div>
                <?php 
                 
                if( !empty($data->workout_summary->avg_hr) ):
                    
                ?><div class="summary-inline summary-lg">
                    <?php echo $data->workout_summary->avg_hr  . '/' . $data->workout_summary->max_hr; ?>
                    <label class="summary-label"><?php echo Mcore::t('Heart Rate'); ?></label>
                </div><?php
                
                endif;
                
                ?>
            </div>
            <div class="clearfix"><hr /></div>
            <table class="table table-clean table-condensed">
                <tr><th></th><th>AVG</th><th>MAX</th></tr>
                <tr>
                    <td><?php echo Mcore::t('Speed'); ?></td>
                    <td><?php echo number_format( $data->workout_summary->avg_speed, 2 ); ?></td>
                    <td></td>
                </tr>
                <tr>
                    <td><?php echo Mcore::t('Cadence'); ?></td>
                    <td><?php echo $data->workout_summary->cadence; ?></td>
                    <td></td>
                </tr>
                <tr>
                    <td><?php echo Mcore::t('Elevation'); ?></td>
                    <td><?php echo floatval($data->workout_summary->ascent); ?></td>
                    <td><?php echo floatval($data->workout_summary->max_altitude); ?></td>
                </tr>
                <tr>
                    <td><?php echo Mcore::t('Power'); ?></td>
                    <td><?php echo floatval($data->workout_summary->avg_watts); ?></td>
                    <td><?php echo floatval($data->workout_summary->max_watts); ?></td>
                </tr>
            </table>
            <p>
                <div class="summary-label"><?php echo Mcore::t('Description'); ?></div>
                <?php echo $data->workout_summary->description; ?>
            </p>
        </div>
        <div class="panel-footer">
            <div class="text-right"><?php
                echo 
                    $data->workout_summary->start_time . ' ',
                    Mcore::t('on') . ' ',
                    $data->workout_summary->date;
            ?></div>
        </div>
    </div>
</div>
<div class="clearfix"></div>