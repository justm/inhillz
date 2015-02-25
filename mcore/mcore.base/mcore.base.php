<?php
/**
 * Súbor obsahuje triedu Mcore
 *
 * @author Matus Macak < matus.macak@folcon.sk > 
 * @link http://www.folcon.sk/
 * @version 1.2 Cute Genie
 * @since Subor je súčasťou aplikácie od verzie 1.0
 * @package mcore.mcore\.base
 *
 */

/**
 * Trieda Mcore - implementácia návrhového vzoru register a singleton
 * 
 * @property Authentication $authenticate 
 * @property Captcha $captcha
 * @property ShopID $shopid
 * @property Mailhandler $mailhandler
 * @property Mysqldatabase $db
 * @property Urlresolver $urlresolver
 * @property Userflash $userflash
 * @property McoreLayout $layout
 * @property Imagemodule $imagemodule
 * @property Cachescript $cachescript
 */
class Mcore {
    
    /**
     * @var Mcore $instance Inštancia triedy
     */
    private static $instance;
    
    /**
     * @var array $objects Pole objektov uchovávaných v registri
     */
    private static $objects = array();
    
    /**
     * @var array $preparedObjects Pole objektov pripravaných na vloženie do registra pred spustením aplikácie
     */
    private static $preparedObjects = array();

    /**
     * @var array $settings Pole nastavení uchovávaných v registri
     */
    private static $settings = array();
    
    /**
     * Slúži na kontrolu vrámci triedy, definuje, či aplikácia bola spustená metódou start()
     * @var bool $isRunning
     */
    private static $isRunning = FALSE;
    
    /**
     * Pole so zoznamom funkcii, ktore budu spustene v metóde start(), pred smerovaním na konkrétny ovládač
     * @var array $afterStartCallbacks
     */
    private $afterStartCallbacks = array();
    
    /**
     * Súkromný konštruktor zabraňujúci priamemu vytvoreniu inštancie triedy
     * @access private
     */
    private function __construct() { }
    
    /**
     * Metóda singleton používaná pre prístup k inštancii triedy
     * @return Mcore 
     */
    public static function base() {
        
        if( !isset( self::$instance ) ){
            $obj = __CLASS__;
            self::$instance = new $obj;
        }
        return self::$instance;
    }
    
    /**
     * Znemožňuje klonovanie objektu
     * 
     * Pri pokuse o klonovanie vyvolá chyu E_USER_ERROR;
     */
    public function __clone() {
        
        trigger_error('Klonovanie objektu je zakázané', E_USER_ERROR);
    }
    
    /**
     * Uloží objekt do registra
     * @param String $object Názov objektu, reps. názov triedy ktorá bude nalinkovaná
     * @param String $key Kľúč v poli
     * @return Bool
     */
    public function storeObject( $object, $key ) {
        
        if( !self::$isRunning ){
            $this->prepareObject( $object, $key );
            return TRUE;
        }
        if( file_exists( self::mPathResolver( 'extend' ) . 'extend/' . $object . '.php' ) ) {
            
            require_once( self::mPathResolver( 'extend' ) . 'extend/' . $object . '.php' );
        }
        else if( file_exists( MCORE_BASE_PATH . 'mcore.base/objects/' . $object . '.class.php' ) ) {
            
            require_once( MCORE_BASE_PATH . 'mcore.base/objects/' . $object . '.class.php' );
        }
        else {
            
            return FALSE;
        }
        $className = ucfirst( $object );
        self::$objects[ $key ] = new $className( self::$instance );
        return TRUE;
    }
    
    /**
     * Objekty, ktoré budú počas štartu aplikácie vložené do registra, sú predbežne zapísané do poľa
     * @param String $object Názov objektu, reps. názov triedy ktorá bude nalinkovaná
     * @param String $key Kľúč v poli
     * @return Bool
     */
    public function prepareObject( $object, $key ) {
        
        self::$preparedObjects[ $key ] = $object;
    }

    /**
     * Vráti požadovaný objekt uložený v registri
     * @param String $key Kľúč, pod ktorým je objekt uložený
     * @return object Objekt
     */
    public function getObject( $key ){
        
        if( array_key_exists( $key, self::$objects ) ){
            return self::$objects[ $key ];
        }
        elseif( self::base()->storeObject( $key, $key ) ){
            return self::$objects[ $key ];
        }
        else{
            trigger_error( "Mcore doesn't store any object under the key {$key}.", E_USER_ERROR );
            return NULL;
        }
    }
    
    /**
     * PHP getter magic method
     * Umožňuje pristupovať k objektom registra cez Mcore::base()->objectName
     * @param String $key Názov objektu uloženého v registry
     */
    public function __get( $key ) {
        
        return $this->getObject($key);
    }
    
    /**
     * Uloží nastavenia do poľa v registry 
     * @param mixed $data Nastavenie, ktoré sa uloží
     * @param String $key Kľúč pod ktorým sa nastavenie uloží a bude dostupné
     * @return void
     */
    public function storeSetting( $data, $key ){
        
        self::$settings[ $key ] = $data;
    }

    /**
     * Získa nastavenie z registra
     * @param String $key Kľúč, pod ktorým je nastavenie uložené
     * @return mixed Nastavenie pod zadaným kľúčom, FALSE ak neexistuje
     */
    public function getSetting( $key ){
        
        if( array_key_exists( $key, self::$settings ) ){
            return self::$settings[ $key ];
        }
        return FALSE;
    }
       
    /**
     * Metoda pre spustenie celej aplikacie
     * @since 1.2 Cute Genie
     * @param array $configs 
     * @return Mcore 
     */
    public static function start( $configs ){
        
        //** Zahájenie relácie
        session_start();

        //** Nastavi hlavicku aby bolo mozne pouzivat subdomeny
        $headers = getallheaders();
        if( !empty( $headers['Origin'] ) && strpos( ROOT_URL, substr( $headers['Origin'], strpos( $headers['Origin'], "." ) + 1 ) ) !== FALSE  ){
            header("Access-Control-Allow-Origin: " . $headers['Origin'] );
            header("Access-Control-Allow-Credentials: true" );
            header('Access-Control-Allow-Headers: x-requested-with, x-requested-by');
        }

        set_error_handler('Mcore::mErrorHandler'); //Spracovanie errorov
        spl_autoload_register('Mcore::mAutoload'); //Autoload suborov pre triedy

        date_default_timezone_set( $configs['default_timezone'] );//casove pasmo
        
        try{
                        
            self::$isRunning = TRUE;
            $base = self::base();
            
            $base->storeSetting( $configs, 'configs' );
            
            foreach ( self::$preparedObjects as $key => $object ){
                $base->storeObject($object, $key);
            }
            
            //** Vytvorenie spojenia s databázou
            $base->getObject('db')->newConnection( 
                        $configs['db']['db_host'],
                        $configs['db']['db_user'],
                        $configs['db']['db_pass'],
                        $configs['db']['db_name']
                    );
            if( is_object( self::$objects['authenticate'] ) ){
                $base->getObject('authenticate')->checkForAuthentication();
            }
            
            foreach ( $base->afterStartCallbacks as $callback ){
                 call_user_func_array( $callback['function'], $callback['params'] );
            }
            
            if( !defined( 'MCORE_INTERFACELANG' ) ){
                define( 'MCORE_INTERFACELANG', 'en' );
            }
            
            $base->getObject('urlresolver')->resolve( $configs['urlRules'] );
        }
        catch ( Exception $e ){
            self::mExceptionHandler( $e );
        }
        
        return self::base();
    }
    
    /**
     * Metoda umožní zaregistrovať funkciu, ktorá budu spustená v metóde start(), 
     * pred smerovaním na konkrétny ovládač. Jedno zavolanie umožňuje zaregistrovať jednu funkciu a jej parametre.
     * V prípade, že je potrebné zaregistrovať viac funkcií, je možné volanie afterStart() opakovať
     * 
     * @since 1.2 Cute Genie
     * @param callable $callback Názov funkcie
     * @param array $params [OPTIONAL] Parametre odoslané do funkcie
     */
    public function afterStart( $callback, $params = array() ){
        
        $this->afterStartCallbacks[] = array(
            'function' => $callback,
            'params' => $params
        );
    }
    
    /**
     * Metóda slúži pre funkcie include, require ako rozhodovací mechanizmus pri pripájaní súborov,
     * ktoré môžu byť `project-specific`, teda cesta k nim je definovaná konštantou MCORE_PROJECT_PATH
     * a daná zložka je definovaná v hlavnom konfiguračnom súbore config.root.php pod kľúčom mcore_project_folders.
     * 
     * Pokiaľ nie je definovaná koštanta MCORE_PROJECT_PATH,
     * zložka nie je uvedená v konfiguračnom súbore, alebo neexistuje,
     * metóda vráti hodnotu konštanty MCORE_APP_PATH, pri úspechu vráti MCORE_PROJECT_PATH
     *  
     * @param String $folder
     * @return String MCORE_PROJECT_PATH | MCORE_APP_PATH
     */
    public static function mPathResolver( $folder ){
        
        if( array_key_exists( 'mcore_project_folders', self::base()->getSetting('configs') ) 
                && array_search( $folder, self::base()->getSetting( 'configs' )['mcore_project_folders'] ) !== FALSE ){
           
            if( defined( 'MCORE_PROJECT_PATH' ) 
                    && file_exists( MCORE_PROJECT_PATH . $folder ) ){
                
                return MCORE_PROJECT_PATH;
            }
        }
        return MCORE_APP_PATH;
    }
    
    /**
    * Funkcia na základe názvu triedy vyhľadá subor v konkrétnom priečinku a pripojí súbor
    * Autoload funguje na triedy - controllers, models, preťažné triedy v priečinku extend alebo objekty registra
    * @param String $className Názov triedy
    * @param bool $throw Definuje či funkcia vyhodí výnimku ak triedu nenájde
    */
    public static function mAutoload( $className ) {

        $arr = preg_split( '/(?=[A-Z])/', $className );
        $suffix = array_pop( $arr );
        $class = str_replace( $suffix, '', $className );
        $file = '';
        switch (strtolower($suffix)) {    
                case 'model':
                        $file = self::mPathResolver( 'models' ) . 'models/' . strtolower( $class ) . '.model.php';
                    break;
                case 'controller':
                        $file = self::mPathResolver( 'controllers' ) . 'controllers/' . strtolower( $class ) . '.controller.php';
                    break;
                case 'core':
                        $file = MCORE_BASE_PATH . 'mabstract/' . strtolower( $class ) . '.php';
                    break;
                default:
                        if( !file_exists( $file = self::mPathResolver( 'extend' ) . 'extend/' . strtolower( $className ) . '.php' ) ){
                            $file = MCORE_BASE_PATH . 'mcore.base/objects/' . strtolower( $className ) . '.class.php';
                        }
                    break;
            }

        //** Ak súbor existuje, je načítaný, ak nie vyhodí chybu E_USER_ERROR
        if ( file_exists( $file ) ) {
            include_once( $file );       
        }
        else {
            trigger_error( "Súbor '$file', obsahujúci triedu '$className' nebol nájdený.", E_USER_ERROR );
        }
    }
    
    /**
     * Funkcia pre zalogovanie errorov, warningov a noticov do suboru
     * @param int $errno
     * @param String $errstr
     * @param String $errfile
     * @param int $errline
     * @return boolean
     */
    public static function mErrorHandler($errno, $errstr, $errfile, $errline) {
        
        if( self::$instance->getSetting( 'DEBUG_LOG_ERRORS' ) ){
            echo '';
            $eMsg = PHP_EOL . '[' . date('d.m.Y H:i:s') . '], IP:[' . $_SERVER['REMOTE_ADDR'] . '],' . PHP_EOL;
            switch ($errno) {
                case E_USER_ERROR:
                case E_ERROR:
                    $eMsg .= "ERROR [$errno] $errstr" . PHP_EOL; 
                    $eMsg .= "Fatal error on line $errline in file $errfile" . PHP_EOL;
                    break;
                case E_USER_WARNING:
                case E_WARNING:
                    $eMsg .= "WARNING [$errno] $errstr" . PHP_EOL;
                    $eMsg .= "Warning on line $errline in file $errfile" . PHP_EOL;
                    break;
                case E_USER_NOTICE:
                case E_NOTICE:
                    $eMsg .=  "NOTICE [$errno] $errstr" . PHP_EOL;
                    $eMsg .= "Notice on line $errline in file $errfile" . PHP_EOL;
                    break;

                default:
                    $eMsg .= "UNKNOWN ERROR type: [$errno] $errstr" . PHP_EOL;
                    $eMsg .= "Unknown error on line $errline in file $errfile" . PHP_EOL;
                    break;
            }

            ob_start();
            debug_print_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
            $eMsg .= 'DEBUG TRACE: ' . ob_get_clean();

            $file = self::$instance->getSetting( 'DEBUG_LOG_PATH' ) . 'error.log.txt';
            
            if( file_exists( $file ) ) {
                file_put_contents( $file, $eMsg, FILE_APPEND | LOCK_EX );
            }

            if( $errno == E_ERROR || $errno == E_USER_ERROR ) {
                $page = new PageController();
                $page->error( 500, self::t('Unfortunately, an error occured'), TRUE );
            }
        }
        //** Don't execute PHP internal error handler
        return true;
        
    }
    
    /**
     * Funkcia pre zalogovanie výnimiek vyhodených aplikáciou
     * @param Exception $e
     */
    public static function mExceptionHandler($e) {

        if( self::$instance->getSetting( 'DEBUG_LOG_EXCEPTIONS' ) ){
            
            $eMsg = '[' . date('d.m.Y H:i:s') . '], IP:[' . $_SERVER['REMOTE_ADDR'] . '],' . PHP_EOL;
            $eMsg .= 'Caught exception: ' . $e->getMessage() . ', in file ' . $e->getFile() . ' on line ' . $e->getLine() . PHP_EOL;
            $eMsg .= 'Debug trace: \n' . $e->getTraceAsString() . PHP_EOL . PHP_EOL; 

            $file = self::$instance->getSetting( 'DEBUG_LOG_PATH' ) . 'exception.log.txt';
            
            if( file_exists( $file ) ) {
                file_put_contents( $file, $eMsg, FILE_APPEND | LOCK_EX );
            }
            
            $page = new PageController();
            $page->error( 500, self::t('Unfortunately, an error occured'), TRUE );
        }
    }
    
    /**
     * Metoda ktora na zaklade jazyka vyhlada subor a konkretny preklad k vstupnemu retazcu
     * @param String $string
     * @param String $file
     * @param array $variables Hodnoty premennych v retazci, Asociativne pole - nazov v retazci => hodnota
     * @return String Prelozene slovo
     */
    public static function t( $string, $file = 'global', $variables = array() ){
        
        $filePath = MCORE_TRANSLATES_PATH . MCORE_INTERFACELANG . '/' . $file . '.php';
        
        if( file_exists( $filePath ) ){
            include $filePath;
            
            if( array_key_exists( $string, $translates ) ){
                return str_replace( array_keys($variables), array_values($variables), $translates[$string] );
            }else{
                return str_replace( array_keys($variables), array_values($variables), $string );
            }
        }
        return $string;
    }
    
    /**
     * Dump vypis zabaleny v <pre> HTML tagoch
     * @param $expression mixed
     */
    public static function var_dump( $expression ){
        
        echo 
            '<pre>',
            var_dump( $expression ),
            '</pre>';    
    }
}