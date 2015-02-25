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
<<<<<<< HEAD
define( 'APP_URL', 'http://localhost/inhillz/'); //URL adresa frameworku a e-commerce aplikácie
define( 'ROOT_URL', 'http://localhost/inhillz/'); //URL adresa špecifického projektu
define( 'ENTRY_SCRIPT_URL', 'http://localhost/inhillz/index.php/');

define( 'UPLOADS_PATH', dirname( __FILE__ ) . '/uploads/');
=======
define( 'APP_URL', 'http://ride.inhillz.com/'); //URL adresa frameworku a e-commerce aplikácie
define( 'ROOT_URL', 'http://ride.inhillz.com/'); //URL adresa špecifického projektu
define( 'ENTRY_SCRIPT_URL', 'http://ride.inhillz.com/');

define( 'UPLOADS_PATH', dirname( __FILE__ ) . '/../uploads/');
>>>>>>> 58f5ff4abc7d58a1f4e5acaed5ed8d4c5530b5b4

define( 'SU_ACCESSKEY', '93bbb6efef128b33fdf073dcb4e4257b' ); //super user access key

define( 'MCORE_TRANSLATES_PATH', dirname(__FILE__) . '/others/translates/');

define( 'EPOCH_TIMESTAMP_OFFSET', 631065600);

/**
 * Pole s konfiguráciu, ktoré je počas behu applikácie dostupné cez Mcore:getSetting('configs')
 */
$configs = array(
    'db' => array(
        'db_host' => 'localhost',
<<<<<<< HEAD
        'db_user' => 'root',
        'db_pass' => '', 
        'db_name' => 'mojtrening',
=======
        'db_user' => 'inhillz',
        'db_pass' => '7frokhoub', 
        'db_name' => 'hillzsqldb',
>>>>>>> 58f5ff4abc7d58a1f4e5acaed5ed8d4c5530b5b4
    ),
    
    'default_timezone' => 'UTC',
);

