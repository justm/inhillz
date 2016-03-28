<?php
/**
 * Súbor predstavuje vstupný bod celej aplikácie
 *
 * @package    root
 * @author     Matus Macak <matus.macak@orchidsphere.com>
 * @link       http://ride.inhillz.com/
 * @version    2.0
 * 
 */

//** Výpis chybových oznámení PHP
error_reporting( E_ALL ); 
ini_set('display_errors', 1);

use \orchidphp\Orchid;

define('VENDOR_PATH', __DIR__ . '/../../orchidcore/orchidcore/vendor/'); //dev
//define('VENDOR_PATH', __DIR__ . '/orchidcore/vendor/'); // production

require 'config/main.config.php';
require VENDOR_PATH . 'orchidsphere/orchidphp/Orchid.php';

Orchid::base()->autoloader()->addNamespacesArray([
    'orchidsphere'        => VENDOR_PATH . 'orchidsphere/',
], true);

Orchid::base()->prepareObject('\inhillz\components\Authentication', 'authenticate');

/**
 * Štart aplikácie
 */
Orchid::base()->start($configs);
 