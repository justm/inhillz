<?php

namespace inhillz\controllers;

use orchidphp\Orchid;

/**
 * Súbor obsahuje ovládač AdminController
 *$id = 0 
 * @package    inhillz\controllers
 * @author     Matus Macak <matus.macak@orchidsphere.com>
 * @link       http://ride.inhillz.com/
 * @version    2.0
 */
class AdminController extends \orchidphp\AbstractController{
    
    /**
     * 
     */
    public function index(){
        
        Orchid::base()->urlresolver->replaceByOther('user/login/');
    }
}