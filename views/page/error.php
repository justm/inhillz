<?php

use orchidphp\Orchid;

/**
 * Súbor predstavuje náhľad pre zobrazenie errorov a neplatných požiadaviek klienta
 *
 * @package    inhillz\views
 * @author     Matus Macak <matus.macak@orchidsphere.com>
 * @link       http://ride.inhillz.com/
 * @version    2.0
 * 
 */
?>
<div class="col-xs-12 col-sm-6">
    <h1 style="font-size: 170px"><?php echo $data->code ?></h1>
</div>
<div class="col-xs-12 col-sm-6">
    <h2 style="font-size: 25px"><?php echo Orchid::t('Well, this is unfortunate')?></h2>
    <?php
        echo (( $data->flash != '')? $data->flash : Orchid::t('The page that you were looking for was not found on this server')) . '.';
    ?>
</div> 