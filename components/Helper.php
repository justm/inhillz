<?php

namespace inhillz\components;

use orchidphp\Orchid;

/**
 * Súbor obsahuje triedu Helper pre pomocné operácie
 *
 * @package    inhillz\components
 * @author     Matus Macak <matus.macak@orchidsphere.com>
 * @link       http://ride.inhillz.com/
 * @version    2.0
 * 
 */
class Helper extends \orchidphp\AbstractController{
    
    /**
     * Hodnota na prepočítanie stupňov do semicircles
     * @var float 
     */
    public static $semic_to_deg = 45/536870912;
    
    /**
     * Metoda uploaduje jeden súbor do zvoleného podadresára v adresári uploads.
     * @param $_FILE $file
     * @param string $subdirectory Podaresár, alebo aj viac úrovní
     * @param array $supported Pole s podporovanými typmi
     * @param array $error Flag, v ktorom je uvedená chybová hláška v prípade že upload zlyhá
     * 
     * @return String|FALSE cesta k súboru, FALSE ak sa súbor upload zlyhá
     */
    public static function uploadFile($file, $subdirectory, $supported, &$error){
                
        $uploadPath = 'uploads/' . $subdirectory . DIRECTORY_SEPARATOR;
        
        $extension  = pathinfo($file['name'], PATHINFO_EXTENSION);
                 
        if(array_search(strtolower($extension), $supported) === FALSE){
            $error[] = Orchid::t('{FILE} is not supported file type', 'global', array('{FILE}' => $file['name']));
            return FALSE;
        }
        else if(move_uploaded_file($file['tmp_name'], PROJECT_PATH . $uploadPath . $file['name'])){
                  
            return PROJECT_PATH . $uploadPath . $file['name']; //Vytvorenie URL adresy
        }
        $error[] = Orchid::t('Uploading failed for file {FILE}', 'global', array('{FILE}' => $file['name']));
        return FALSE;
    }
    
    /**
     * Konverzia jednotiek
     * @param mixed $value
     * @param String $in_unit
     * @param String $out_unit
     * @return mixed
     */
    public static function convertUnits($value, $in_unit, $out_unit){
                
        $conversion = array(
            'm/s' => array('km/h' => 3.6,
                           'mph' =>  2.23693629
                    ),
            'm'   => array('km' => 0.001,
                           'mi' => 0.00062137),
        );
        
        if(isset($conversion[$in_unit]) && isset($conversion[$in_unit][$out_unit])){
            
            return $value * $conversion[$in_unit][$out_unit];
        }
        
        return $value;
    }
    
    /**
     * Hash funkcia, pre tvorbu uniformných stringov
     * @param String $string
     * @return String
     */
    public static function getHash($string){
        
        return sha1("RW8DLG" . $string);
    }
    
    /**
     * Vytovrí HTML div so správou
     * @param string $message
     * @param string $type
     */
    public static function echoAlert($message, $type){
        
        echo
            '<div class="alert ' . $type . '">' . $message . '</div>';
    }
    
    /**
     * Vrati pole pozícii z tréningového záznamu
     * @param array $record
     * @return array
     */
    public static function getPositionsFromRecord($record){

        $points = array_map(
                    function($point){
                        if(isset($point['position_lat']) && isset($point['position_long'])){
                            return ($point['position_lat'] * (45/536870912)) . ',' . ($point['position_long'] * (45/536870912));
                        }
                    }, $record);
                            
        return self::array_trim($points);
    }
    
    /**
     * Redukuje vstupne pole na NOT NULL prvky
     * @param array $array
     * @return array
     */
    public static function array_trim($array){
        
        return array_filter($array, function ($v){ return !empty($v); });
    }
    
    /**
     * Vypocita vzdialenost medzi 2 polohami
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     * @return float in meters
     */
    public static function haversineDistance($lat1, $lon1, $lat2, $lon2) {
        
        $p = 0.017453292519943295;    // PI / 180
        $a = 0.5 - cos(($lat2 - $lat1) * $p)/2 
           + cos($lat1 * $p) * cos($lat2 * $p) 
           * (1 - cos(($lon2 - $lon1) * $p))/2;

        return 12742 * 1000 * asin(sqrt($a)); // 12742 = 2 * R; R = 6371 km
    }
}