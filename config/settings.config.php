<?php
/**
 * Konfiguračný s nastaveniami ukladanými cez Mcore::storeSetting()
 *
 * @author Matus Macak < matus.macak@folcon.sk > 
 * @link http://www.folcon.sk/
 * @version 2.0
 * @since Subor je súčasťou aplikácie od verzie 2.0
 * @package root
 * 
 */

$mcore->storeSetting( TRUE, 'DEBUG_LOG_EXCEPTIONS' ); // logovanie vynimiek
$mcore->storeSetting( TRUE,  'DEBUG_LOG_ERRORS' ); //logovanie errorov
$mcore->storeSetting( dirname( __FILE__ ) . '/../others/logs/', 'DEBUG_LOG_PATH' ); //cesta k logom

$mcore->storeSetting( FALSE,  'MCORE_CACHETEMPLATE_USE' );
$mcore->storeSetting( 1440,  'MCORE_CACHETEMPLATE_REFRESH' ); //cache refresh v minútach
$mcore->storeSetting( FALSE, 'MCORE_SUBDOMAIN_USE' ); //používanie subdomén
