<?php
/**
 * Súbor predstavuje náhľad pre zobrazenie errorov a neplatných požiadaviek klienta
 *
 * @author Matus Macak < matusmacak@justm.sk > 
 * @link http://www.folcon.sk/
 * @version 1.1 Mercury drop
 * @since Subor je súčasťou aplikácie od verzie 1.0
 * @package views.page
 * 
 */

?>
<span id="error">
<div class="left">
    <h1 style="font-size: 170px"><?php echo $data->code ?></h1>
</div>
<div class="rightOvf">
    <h2 style="font-size: 25px"><?php echo Mcore::t('Well, this is unfortunate')?></h2>
    <?php
        echo (( $data->flash != '')? $data->flash : Mcore::t('The page that you were looking for was not found on this server')) . '.';
    ?> 
</div>