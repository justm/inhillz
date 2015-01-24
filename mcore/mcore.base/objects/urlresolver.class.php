<?php
/**
 * Súbor obsahuje triedu Urlresolver
 *
 * @author Matus Macak < matus.macak@folcon.sk > 
 * @link http://www.folcon.sk/
 * @version 1.1 Mercury drop
 * @since Subor je súčasťou aplikácie od verzie 1.0
 * @package mcore.mcore\.base.objects
 *
 */

/**
 * Trieda Urlresolver obsahuje metódy pre smerovanie požiadavky na správny ovládač a akciu
 */
class Urlresolver {
    
    /**
     * @var String
     */
    protected $urlPath;
    
    /**
     * @var array
     */
    protected $urlBits = array();
    
    /**
     * URL adresa z ktorej odchádzala požiadavka prijatá serverom $_SERVER['HTTP_REFERER']
     */
    protected $httpReferer;
    
    /**
     * Konštruktor objektu
     */
    public function __construct() { }
    
    /**
     * Metoda vrati $urlBits aby bolo mozne v ovladaci spracovat dalsie parametre URL adresy
     * @param $offset Definuje od ktoreho prvu pola sa vezme obsah pola
     * @return array Parametre url odresy od
     */
    public function getUrlBits( $offset ) {
        return array_slice( $this->urlBits, $offset );
    }

    /**
     * Metóda na základe url adresy, aplikačných a configuračných pravidiel smeruje
     * aplikáciu na potrebný ovládač
     * @param array $urlRules Specificke pravidala, ktoré majú byť uplatené
     * @return void
     * 
     * @todo Spracovanie errorov
     */
    public function resolve( $urlRules, $urldata = NULL ) {
                    
        $this->parseURL( $urldata );
        
        if( file_exists( MCORE_APP_PATH . 'controllers/' . $this->urlBits[0] . '.controller.php' ) ) {

            //Ovládač existuje, pre nekonfigurované mená ovládačov, AJAX requesty...
            $controllerClass = ucfirst( $this->urlBits[0] ) . 'Controller';
            
            if( isset( $this->urlBits[1] ) && $this->urlBits[1] != '' ){
                if( method_exists( $controllerClass, $action = $this->urlBits[1] ) ){
                    if( isset( $this->urlBits[2] ) ) {
                        $this->makeTowards( $controllerClass, $action, $this->urlBits[2] );
                    }
                    else{
                        $this->makeTowards( $controllerClass, $action );
                    }
                }
                else{
                    $this->makeTowards( 'PageController', 'error', 404 );
                }
            }
            elseif( method_exists( $controllerClass, 'index' ) ) {
                $this->makeTowards( $controllerClass, 'index' );
            }
            else{
                $this->makeTowards( 'PageController', 'error', 404 );
            }
        }
        else{ //Skúsi hľadať podľa konfiguračných pravidiel
            $check = 0;
            
            foreach( (array) $urlRules as $pattern => $callback) {
                
                if ( preg_match( $pattern, $this->urlPath, $matches) ) {
                    if( isset( $matches['url'] ) && $matches['url'] != '' ){
                        $this->makeTowards($callback[0], $callback[1], $matches['url']);
                    }
                    else if( isset( $callback[2] ) ){
                        $this->makeTowards($callback[0], $callback[1], $callback[2]);
                    }
                    else{
                        $this->makeTowards($callback[0], $callback[1]);
                    }
                    $check++;
                }
            }
            //Ak nič nenájde zobrazí error
            if( $check == 0 ){
                $this->makeTowards( 'PageController', 'error', 404 );
            }
        }
    }
    
    /**
     * Metóda nahradí obsah pôvodnej, ktorý bol požadovaný obsahom z inej adresy URL, 
     * URL sa však nezmení. Nejedná sa teda o presmerovanie
     * 
     * @param String $url vo formáte ovládač/akcia. 
     */
    public function replaceByOther( $url ){
        
        if( substr($url, 0, 1) !== '/' ){
           $url = '/' . $url;
        }
        $this->resolve( Mcore::base()->getSetting( 'urlRules' ), $url );
    }
    
    /**
     * Presmeruje aplikáciu na zadanú URL adresu v tvare ovládač/akcia/id
     * 
     * @param String $url vo formáte ovládač/akcia. Vstupný skript je doplnený
     * @param integer $statusCode [optional] See {@link http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html}
     */
    public function redirect( $url, $statusCode = 301 ){
        
        if( substr($url, 0, 1) == '/' ){
            $url = substr( $url, 1 );
        }
        header( 'Location: ' . ENTRY_SCRIPT_URL . $url, true, $statusCode );
        exit();
    }
    
    /**
     * Podobne ako metoda redirect() presmeruje požiadavku, avšak ako adresu vezme hodnotu httpReferer
     * Ak nie je nastavena, presmeruje na hlavnu stranku
     * 
     * @param integer $statusCode [optional] See {@link http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html}
     */
    public function redirectBack( $statusCode = 301 ){
        
        $referer = $this->getHttpReferer();
        
        if( !isset( $referer ) ){
            $referer = ENTRY_SCRIPT_URL;
        }
        header( 'Location: '.$referer, true, $statusCode );
        exit();
    }
    
    /**
     * Vráti premennú hodnotu $_SERVER['HTTP_REFERER']
     * @return string|null
     */
    public function getHttpReferer() {
        
        return isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : NULL;
    }
    
    /**
     * Vrati boolean hodnotu, ktora definuje ci je alebo nie je nastavena URL adresa pre krok späť
     * @since version 1.1 Mercury drop
     * @return type
     */
    public function issetMoveback(){
        
        return empty( $_SESSION['magnecomf_url_moveback'] );
    }
    
    /**
     * Vrati boolean hodnotu, ktora definuje ci je alebo nie je nastavena URL adresa pre krok späť
     * @since version 1.1 Mercury drop
     * @return type
     */
    public function unsetMoveback(){
        
        unset( $_SESSION['magnecomf_url_moveback'] );
        unset( $_SESSION['magnecomf_url_moveback_refaddress'] );
    }
    /**
     * Nastavi URL adresu pre pohyb na stranke o krok späť
     * @since version 1.1 Mercury drop
     * @param string $ifNull URL adresa, ktora sa použije ak z nejakeho dôvodu nie je definovany refferer
     */
    public function setMoveback( $ifNull ){
        
        if( $_SESSION['magnecomf_url_moveback_refaddress'] != implode( '/', array_slice( $this->urlBits, 0, 3 ) ) ){
            $moveBack = $this->getHttpReferer();
            $_SESSION['magnecomf_url_moveback'] = !empty( $moveBack )? $moveBack : $ifNull;
            $_SESSION['magnecomf_url_moveback_refaddress'] = implode( '/', array_slice( $this->urlBits, 0, 3 ) );
        }
    }
    
    /**
     * Vrati URL adresu pre navrat späť, pre pohyb na stránke
     * 
     * @return string URL adresa pre navrat späť alebo # ak nie je nastavena
     */
    public function getMoveback(){
        
        if( !empty( $_SESSION['magnecomf_url_moveback'] ) ){
            $moveBack = $_SESSION['magnecomf_url_moveback'];
            return $moveBack;
        }
        else{
            return '#';
        }
    }
 
    /**
     * Metóda získa požadovanú cestu z adresy URL, inicializuje atribúty objektu
     * Urlresolver
     * 
     * @param string $urldata Ak nie je zadana použije sa $_SERVER['REQUEST_URI']
     * @return void
     */
    protected function parseURL( $urldata = NULL ) {
        
        if( !isset( $urldata ) ){
            $urldata = $_SERVER['REQUEST_URI'];
        }
        $this->urlBits = array();
        
        //** Parsne subdomenu
        $request = str_replace( 'www.', '', $_SERVER['SERVER_NAME'] ) . '/';  
        $main = str_replace( 'www.', '', parse_url(ROOT_URL)['host']);
        
        $tmp = substr( $request, 0, strpos( $request, $main ) );
        $subdomain = ( substr($tmp, -1) == '.' ) ?  substr($tmp, 0, -1) : $tmp;
        
        //** Ak je celá aplikácia v podzložke, z cesty sa odstráni podzložka
        if( strpos($urldata, basename(ROOT_URL)) !== FALSE ){ 
            $urldata = substr($urldata, strpos($urldata, basename(ROOT_URL)) + strlen(basename(ROOT_URL)));
        }
        
        $parsed = preg_split('/[\?,\/]/' , $urldata); //parsuje požiadavku
        array_shift($parsed); //odoberie prvu prazdnu hodnotu
        if( isset($parsed[0]) && $parsed[0] == 'index.php' ){
            array_shift($parsed);
        }
        
        if( !empty( $subdomain ) ){
            $this->urlBits[] = $subdomain;
        }
        if( !empty( $parsed[0] ) || !empty( $subdomain )){
            $this->urlBits = array_merge( $this->urlBits, $parsed );
        }
        else{ //žiadne argumenty => page/index
            $this->urlBits[] = 'page';
        }
        
        //** Vycisti pripadne pokusy a o hack DB
        $sanitizer = Mcore::base()->db;
        foreach ( $this->urlBits as $key => $bit ){
            $this->urlBits[$key] = $sanitizer->sanitizeData( $bit );
        }
        
        $this->urlPath = implode('/', $this->urlBits);
    }
    
    /**
     * @deprecated since version 1.1 
     * @return array
     */
    protected function array_trim($array) {
        
        while (!empty($array) and strlen(reset($array)) === 0) {
            array_shift($array);
        }
        while (!empty($array) and strlen(end($array)) === 0) {
            array_pop($array);
        }
        return $array;
    }
    
    /**
     * Metóda smeruje požiadavku na konkrétny ovládač a akciu
     * @param String $controllerClass Presný názov triedy ovládača
     * @param String $action Presný názov akcie
     * @param mixed $id
     * @param array $params
     */
    protected function makeTowards( $controllerClass, $action, $id = '', $params = array() ) {
        
        $controller = new $controllerClass();
        if( $id != '' ){
            $controller->$action($id);
        }
        else{
            $controller->$action();
        }
    }
    
    /**
     * Zoznam kódov pre vytvorenie hlavičky do error stránky
     */
    public function getHeaderPhrase( $code ){
         $phrases = array(
            '100'  => 'Continue',
            '101' => 'Switching Protocols',
            '200' => 'OK',
            '201' => 'Created',
            '202' => 'Accepted',
            '203' => 'Non-Authoritative Information',
            '204' => 'No Content',
            '205' => 'Reset Content',
            '206' => 'Partial Content',
            '300' => 'Multiple Choices',
            '301' => 'Moved Permanently',
            '302' => 'Found',
            '303' => 'See Other',
            '304' => 'Not Modified',
            '305'  => 'Use Proxy',
            '307' => 'Temporary Redirect',
            '400' => 'Bad Request',
            '401'  => 'Unauthorized',
            '402'  => 'Payment Required',
            '403' => 'Forbidden',
            '404'  => 'Not Found',
            '405'  => 'Method Not Allowed',
            '406'  => 'Not Acceptable',
            '407'  => 'Proxy Authentication Required',
            '408'  => 'Request Time-out',
            '409'  => 'Conflict',
            '410'  => 'Gone',
            '411'  => 'Length Required',
            '412'  => 'Precondition Failed',
            '413'  => 'Request Entity Too Large',
            '414'  => 'Request-URI Too Large',
            '415'  => 'Unsupported Media Type',
            '416'  => 'Requested range not satisfiable',
            '417'  => 'Expectation Failed',
            '500'  => 'Internal Server Error',
            '501'  => 'Not Implemented',
            '502'  => 'Bad Gateway',
            '503'  => 'Service Unavailable',
            '504'  => 'Gateway Time-out',
            '505'  => 'HTTP Version not supported',
         );
         
         if( array_key_exists( $code, $phrases ) ){
             return $code . ' ' . $phrases[$code];
         }
         else{
             return '302 ' . $phrases['302'];
         }
    }
}