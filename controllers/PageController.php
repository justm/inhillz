<?php

namespace inhillz\controllers;

use orchidphp\Orchid;

/**
 * Súbor obsahuje ovládač PageController
 *
 * @package    inhillz\controllers
 * @author     Matus Macak <matus.macak@orchidsphere.com>
 * @link       http://ride.inhillz.com/
 * @version    2.0
 */
class PageController extends AbstractWebController{
    
    /**
     * @inheritdoc
     */
    public $seoTitle = 'Cycling performance analyzer';
    
    /**
     * @inheritdoc
     */
    public function accessRules() {
        
        return array(
            
        );
    }
    
    public static function controller(){
        
        return new PageController();
    }
    
    /**
     * Zobrazenie hlavnej stránky, HOMEPAGE
     */
    public function index() {        

        $this->render('index');
    }
    
    /**
     * Metóda pre spracovanie a zobrazenie errorov aplikácie
     * @param int $code Kód chyby
     * @param string $flash Správa zobrazená používateľovi
     * @param boolean $showPlain Rozhodne či sa zobrazí aj vonkajší kontajner
     */
    public function error( $code, $flash = '', $showPlain = FALSE ){
        
        $message = Orchid::base()->getObject('urlresolver')->getHeaderPhrase( $code );
        header("HTTP/1.0 {$message}");
        
        if( $showPlain ){
            $this->render( 'error', array( 'code'=> $code, 'flash' => $flash ) );
        }
        else{
            $this->render( 'error', array( 'code'=> $code, 'flash' => $flash ) );
        }
        exit(1);
    }
}
