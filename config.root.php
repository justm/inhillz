<?php
/**
 * Konfiguračný súbor aplikácie
 *
 * @author Matus Macak < matus.macak@folcon.sk > 
 * @link http://www.folcon.sk/
 * @version 1.2 Cute Genie
 * @since Subor je súčasťou aplikácie od verzie 1.0
 * @package root
 * 
 */

//**Globálne konštanty
define( 'APP_URL', 'http://localhost/inhillz/'); //URL adresa frameworku a e-commerce aplikácie
define( 'ROOT_URL', 'http://localhost/inhillz/'); //URL adresa špecifického projektu
define( 'ENTRY_SCRIPT_URL', 'http://localhost/inhillz/index.php/');

define( 'UPLOADS_PATH', dirname( __FILE__ ) . '/../uploads/');

define( 'SU_ACCESSKEY', '93bbb6efef128b33fdf073dcb4e4257b' ); //super user access key

define( 'MCORE_TRANSLATES_PATH', dirname(__FILE__) . '/others/translates/');

/**
 * Pole s konfiguráciu, ktoré je počas behu applikácie dostupné cez Mcore:getSetting('configs')
 */
$configs = array(
    'db' => array(
        'db_host' => 'localhost',
        'db_user' => 'root',
        'db_pass' => '', 
        'db_name' => 'mojtrening',
    ),
    
    'default_timezone' => 'Europe/Bratislava',
);

