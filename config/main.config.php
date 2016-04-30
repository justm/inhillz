<?php
/**
 * Konfiguračný súbor
 *
 * @package    inhillz\config
 * @author     Matus Macak <matus.macak@orchidsphere.com>
 * @link       http://ride.inhillz.com/
 * @version    2.0
 * 
 */

define('APP_NAMESPACE', 'inhillz');
define('APP_PATH', __DIR__ . '/../');
define('PROJECT_PATH', __DIR__ . '/../');

//** Dev
define( 'APP_URL', 'http://localhost/inhillz/dp/'); 
define( 'ROOT_URL', 'http://localhost/inhillz/dp/'); 
define( 'ENTRY_SCRIPT_URL', 'http://localhost/inhillz/dp/index.php/');

//** Production
//define('APP_URL', 'http://ride.inhillz.com/'); 
//define('ROOT_URL', 'http://ride.inhillz.com/'); 
//define('ENTRY_SCRIPT_URL', 'http://ride.inhillz.com/');
//**

define('UPLOADS_PATH', dirname( __FILE__ ) . '/uploads/');
define('SU_ACCESSKEY', '93bbb6efef128b33fdf073dcb4e4257b' ); //super user access key
define('MCORE_TRANSLATES_PATH', dirname(__FILE__) . '/others/translates/');
define('EPOCH_TIMESTAMP_OFFSET', 631065600);

$configs = [
    'db' => [
//        'db_dns'  => 'mysql:host=localhost;dbname=mojtrening;charset=utf8',
//        'db_host' => 'localhost',
//        'db_user' => 'root',
//        'db_pass' => '', 
//        'db_name' => 'mojtrening',
        //** Production
        'db_dns'  => 'mysql:host=37.9.170.84;dbname=hillzsqldb;charset=utf8',
        'db_host' => '37.9.170.84', 
        'db_user' => 'inhillz',
        'db_pass' => '7frokhoub', 
        'db_name' => 'hillzsqldb',
    ],
    'urlResolver' => [
        'urlRules' => [ 
            //specific URL routing
        ],
        'trailingSlash' => TRUE,
        'errorHandler' => 'page/error',
        'userLogin' => 'user/login',
        'subdomains' => FALSE,
    ],
    'page' => [
        'name' => 'InHillz',
        'breadcrumbRoot' => 'InHillz',
        'default_timezone' => 'UTC',
    ],
    'phpmailer' => [
        'server' => 'smtp.websupport.sk',
        'encryption' => 'ssl',
        'port' => 465,
        'info@inhillz.sk' => 'passwd_here',
    ],
    'params' => [
        
    ],
];
