<?php
/**
 * Súbor predstavuje vstupný bod celej aplikácie
 *
 * @author Matus Macak < matus.macak@folcon.sk > 
 * @link http://www.folcon.sk/
 * @version 2.0
 * @since Subor je súčasťou aplikácie od verzie 2.0
 * @package root
 * 
 */

//** Výpis chybových oznámení PHP
error_reporting( E_ERROR | E_WARNING | E_PARSE ); 
ini_set('display_errors', 1);


define( 'MCORE_BASE_PATH', dirname( __FILE__ ) . '/mcore/'); //jadro frameworku
define( 'MCORE_APP_PATH', dirname( __FILE__ ) . '/'); // controllers, libraries, javascript, models
define( 'MCORE_PROJECT_PATH', dirname( __FILE__ ) . '/'); // project specific files = config, extend, style, views, 
define( 'MCORE_INTERFACELANG', 'en' );

require_once( MCORE_PROJECT_PATH . 'config.root.php' );
require_once( MCORE_PROJECT_PATH . "config/main.config.php" );

require_once( MCORE_BASE_PATH . '/mcore.base/mcore.base.php' );
$mcore = Mcore::base();
        
//** Nižšie sú zaregistrované povinné objekty aplikácie
$mcore->prepareObject( 'mysqldatabase', 'db' );
$mcore->prepareObject( 'urlresolver', 'urlresolver');

//** Voliteľné rozšírenia
$mcore->prepareObject( 'authentication', 'authenticate');
$mcore->prepareObject( 'userflash', 'userflash');

//** Nastavenia
include MCORE_PROJECT_PATH . 'config/settings.config.php';
 
/**
 * Štart aplikácie
 * !!! Neuvadzať žiadne echo alebo print vypisy nad tieto riadky !!!
 */
$mcore->start( array_merge( $configs, $additional_configs ) );
/**
 * !!! Ďalšie nastavenie alebo objekty registrované po spustení aplikácie metódou start() nebudú použíté !!!
 */   