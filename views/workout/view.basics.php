<?php
/**
 * Základné informácie - Časť náhľadu pre detailné zobrazenie tréningu
 *
 * @author Matus Macak < matus.macak@folcon.sk > 
 * @version 2.0
 * @since Subor je súčasťou aplikácie od verzie 2.0
 * @package views.workout
 * 
 * @var WorkoutModel $data->workout_basics
 * @var Array $data->workout_data
 */
?><div class="col-xs-12 col-md-6">
    <div class="panel panel-default" style="height: 300px">
        <div class="panel-heading"><h1 class="h5"><?php echo $data->workout_basics->title; ?></h1></div>
        <div class="panel-body"></div>
    </div>
</div>
<div class="clearfix"></div>