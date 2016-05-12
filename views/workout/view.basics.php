<?php

use inhillz\models\WorkoutModel;
use orchidphp\Orchid;

/**
 * Základné informácie - Časť náhľadu pre detailné zobrazenie tréningu
 *
 * @package    inhillz\views
 * @author     Matus Macak <matus.macak@orchidsphere.com>
 * @link       http://ride.inhillz.com/
 * @version    2.0
 * 
 * @var WorkoutModel $data->workout_summary
 * @var array $data->workout_data
 */
?><div class="col-xs-12 col-md-5">
    <div class="panel panel-info summary" style="min-height: 400px">
        <div class="panel-heading"><h1 class="h5"><?php echo $data->workout_summary->title; ?></h1></div>
        <div class="panel-body">
            <div class="row">
                <div class="summary-inline summary-lg">
                    <?php echo $data->workout_summary->duration; ?>
                    <label class="summary-label"><?php echo Orchid::t('Moving Time'); ?></label>
                </div>
                <div class="summary-inline summary-lg">
                    <?php echo number_format( $data->workout_summary->distance, 1 ); ?><abbr class="unit" title="kilometers">km</abbr>
                    <label class="summary-label"><?php echo Orchid::t('Distance'); ?></label>
                </div>
                <?php 
                 
                if( !empty($data->workout_summary->avg_hr) ):
                    
                ?><div class="summary-inline summary-lg">
                    <?php echo $data->workout_summary->avg_hr  . '/' . $data->workout_summary->max_hr; ?>
                    <label class="summary-label"><?php echo Orchid::t('Heart Rate'); ?></label>
                </div><?php
                
                endif;
                
                ?>
            </div>
            <div class="clearfix"><hr /></div>
            <table class="table table-clean table-condensed">
                <tr><th></th><th>AVG</th><th>MAX</th></tr>
                <tr>
                    <td><?php echo Orchid::t('Speed'); ?></td>
                    <td><?php echo number_format( $data->workout_summary->avg_speed, 2 ); ?></td>
                    <td></td>
                </tr>
                <tr>
                    <td><?php echo Orchid::t('Cadence'); ?></td>
                    <td><?php echo $data->workout_summary->avg_cadence; ?></td>
                    <td><?php echo $data->workout_summary->max_cadence; ?></td>
                </tr>
                <tr>
                    <td><?php echo Orchid::t('Elevation'); ?></td>
                    <td><?php echo floatval($data->workout_summary->ascent); ?></td>
                    <td><?php echo floatval($data->workout_summary->max_altitude); ?></td>
                </tr>
                <tr>
                    <td><?php echo Orchid::t('Power'); ?></td>
                    <td><?php echo floatval($data->workout_summary->avg_watts); ?></td>
                    <td><?php echo floatval($data->workout_summary->max_watts); ?></td>
                </tr>
            </table>
            <p>
                <div class="summary-label"><?php echo Orchid::t('Description'); ?></div>
                <?php echo $data->workout_summary->description; ?>
            </p>
        </div>
        <div class="panel-footer">
            <div class="text-right"><?php
                echo 
                    $data->workout_summary->start_time . ' ',
                    Orchid::t('on') . ' ',
                    $data->workout_summary->date;
            ?></div>
        </div>
    </div>
</div>
<div class="clearfix"></div>