<?php

namespace inhillz\controllers;

use orchidphp\HTMLhelper;
use orchidphp\Orchid;

/**
 * AbstractWebController
 * 
 * @namespace  inhillz\controllers; 
 * @package    \app\controllers
 * @author     Matus Macak <matus.macak@orchidsphere.com>
 * @copyright  2015 OrchidSphere
 * @link       http://orchidsphere.com/
 * @license    License here
 * @version    1.0.0
 */
abstract class AbstractWebController extends \orchidphp\AbstractController{

    /**
     * Web page title
     * @var string
     */
    public $seoTitle = '';
    
    /**
     * If set to FALSE, appends an page name after a title
     * @var boolean 
     */
    public $seoTitleBlankSuffix = FALSE;
    
    /**
     * Page meta description 
     * @var string
     */
    public $seoDesc = '';
    
    /**
     * Path to a file with sidebar content relative to views/ folder
     * @var string 
     */
    public $sidebar = '';
    
    /**
     * @inheritdoc
     */
    public function __construct() {

        if(empty($this->seoTitle)){
            $class = array_pop((explode('\\', get_called_class())));
            $this->seoTitle = str_replace('Controller', '', preg_replace('/(?=[A-Z])/', ' ',$class));
        }
        parent::__construct();
    }
    
    /**
     * Prints the meta content into page header
     */
    public function printMeta() {
        
        echo 
            '<title>',
                HTMLhelper::substr_unicode($this->seoTitle, 0,60),
                !$this->seoTitleBlankSuffix ? (' | ' . Orchid::base()->getSetting(['configs'=>['page'=>'name']])) : '',
            '</title>' . PHP_EOL,
            '<meta name="description" content="' . $this->seoDesc . '"/>'.PHP_EOL;
    }
}
