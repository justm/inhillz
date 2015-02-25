<?php
/**
 * Súbor obsahuje triedu McontrollerCore
 *
 * @author Matus Macak < matus.macak@folcon.sk > 
 * @link http://www.folcon.sk/
 * @version 1.1 Mercury drop
 * @since Subor je súčasťou aplikácie od verzie 1.0
 * @package mcore.mabstract
 * 
 */

/**
 * Trieda MControllerCore predstavuje základnú funckionalitu, ktorú by mal 
 * zdediť každý ovládač v tomto frameworku
 */
abstract class McontrollerCore {
    
    /**
     * @var array Premenné odoslané do náhľadu
     */
    protected $data = array();
    
    /**
     * @var String Podzložka v ktorej sú uložené náhľady asociované s týmto ovládačom
     */
    protected $viewfolder;
    
    /**
     * @var String názov súboru bez prípony, ktorý predstavuje náhľad
     */
    protected $viewfile;
    
    /**
     * @var String Súbor, ktorý sa použíje ako vonkajší kontajner pre zobrazený náhľad 
     */
    protected $template = 'main';
    
    /**
     * Konštruktor triedy priradí do premennej $viewfolder názov podzložky nahľadu
     * podľa názvu triedy
     */
    public function __construct() {
        $folder_exp = preg_split( '/(?=[A-Z])/', get_class($this) );
        array_shift($folder_exp);
        $this->viewfolder = strtolower( array_shift($folder_exp) );
    }
    
    /**
     * Metóda skontroluje prístupové práva a ak používateľ spĺňa úpžadovanú úroveň práv
     * spustí privatnú funkciu, volanú zvonku
     * 
     * @param String $method Názov volanej metódy
     * @param mixed $arguments Argumenty
     */
    public function __call( $method, $arguments ) {
        
        $acRules = $this->accessRules();
        $permission = FALSE;
        foreach ( $acRules as $role => $actions ){
            
            if( array_search( $method, $actions ) !== FALSE
                && $role == Mcore::base()->authenticate->role ) {
                
                $permission = TRUE;
            }
            else if( array_search( $method, $actions ) !== FALSE
                    && $role == '@admin' && Mcore::base()->authenticate->isAdmin() ){

                $permission = TRUE;
            }
            else if( array_search( $method, $actions ) !== FALSE
                    && $role == '@' && Mcore::base()->authenticate->isLoggedIn() ){

                $permission = TRUE;
            }
        }
        if( $permission ){
            call_user_func_array( array( $this, $method ), $arguments );
        }
        else{
            time_nanosleep( 2, 0 );
            header($_SERVER['SERVER_PROTOCOL'] . ' 401 Unauthorized', true, 401);
            Mcore::base()->urlresolver->replaceByOther('admin/index/1');
        }
    }
    
    /**
     * 
     * Metóda definuje prístupové práva k metódam. Každá metóda, ktorá má prejsť kontrolou práv
     * musí byť v rámci triedy definovaná ako protected!
     * 
     *      @ - všetci prihlásený používatelia
     *      @ admin - všetci prihlásený s administrátorskými právami rola
     *      ... dalsie definovane role Stringom
     * 
     * @return array
     */
    public function accessRules() {
        return array(
            '@' => array(), // všetci prihlásený používatelia
            '@admin' => array(), //všetci prihlásený s administrátorskými právami rola
            'admin' => array(), //definovaná rola
        );
    }

    /**
     * Metóda zavolá požadovaný náhľad, odošle premenné a po spracovaní ho zabalí do vonkajšej šablóny 
     * 
     * @param String $viewfile Názov súboru bez prípony ktorý predstavuje náhľad
     * @param Array $variables <i>OPTIONAL</i> Pole premenných
     * @param String $subfolder <i>OPTIONAL</i> Podzložka view v prípade ak je potrebné použiť náhľad z inej podzložky
     */
    public function render($viewfile, $variables = array(), $subfolder = '') {
        
        if( $subfolder == '' ) {
            $subfolder = $this->viewfolder;
        }
        //vytvorí cestu k súboru
        $lang = MCORE_INTERFACELANG;
        $fileLang = Mcore::mPathResolver( 'views' ) . '/views/' . $subfolder . '/' . $lang . '/' . strtolower($viewfile) . '.php';
        $file = Mcore::mPathResolver( 'views' ) . '/views/' . $subfolder . '/' . strtolower($viewfile) . '.php';
        
        if ( file_exists( $fileLang ) ) {
            $this->viewfile = $fileLang;
        }  
        elseif( file_exists( $file ) ){
            $this->viewfile = $file;
        }
        else{
            throw new Exception( "Desired view file {$viewfile} was not found." );
        }
        
        //Uloží prijaté dáta do premennej a použije ich v náhľade
        foreach ( $variables as $key => $value ) {
            $this->data[$key] = $value;
        }
        //Pohodlnejšie je použitie typu objekt
        $data = (object) $this->data;
    
        //pripravi vonkajšiu časť
        ob_start();
        include( $this->viewfile );
        $inner = ob_get_clean();
        
        //Pripraví vonkajšiu časť
        $content = '<content></content>';
        if( !Mcore::base()->getSetting( 'MCORE_CACHETEMPLATE_USE' ) || ( $outer = $this->getCacheTemplate( $this->template ) ) === FALSE ){ 
            
            //** Ak nie je v cache, vytvori novy a ulozi ho do cache
            ob_start();
            include Mcore::mPathResolver( 'views' ) . '/views/default/' . strtolower( $this->template ) . '.php';
            $outer = Mcore::base()->cachescript->flushFiles( ob_get_clean() );
            $this->cacheTemplate( $outer, $this->template );
        }
        
        $outer = Mcore::base()->cachescript->flushCache( $outer );
        echo str_replace( '<content></content>', $inner, $outer);
    }
    
    /**
     * Metóda zavolá požadovaný náhľad a po spracovaní ho zobrazí bez vonkajšej šablóny
     * 
     * @param String $viewfile Názov súboru bez prípony ktorý predstavuje náhľad
     * @param Array $variables [optional] Pole premenných
     * @param String $subfolder [optional] Podzložka view v prípade ak je potrebné použiť náhľad z inej podzložky
     */
    public function renderPartial($viewfile, $variables = array(), $subfolder = '') {
        
        if( $subfolder == '' ) {
            $subfolder = $this->viewfolder;
        }
        //vytvorí cestu k súboru
        $lang = MCORE_INTERFACELANG;
        $fileLang = Mcore::mPathResolver( 'views' ) . '/views/' . $subfolder . '/' . $lang . '/' . strtolower($viewfile) . '.php';
        $file = Mcore::mPathResolver( 'views' ) . '/views/' . $subfolder . '/' . strtolower($viewfile) . '.php';
        
        if ( file_exists( $fileLang ) ) {
            $this->viewfile = $fileLang;
        }  
        elseif( file_exists( $file ) ){
            $this->viewfile = $file;
        }
        else{
            throw new Exception( "Desired view file {$viewfile} was not found." );
        }
        
        //Uloží prijaté dáta do premennej a použije ich v náhľade
        foreach ( $variables as $key => $value ) {
            $this->data[$key] = $value;
        }
        //Pohodlnejšie je použitie typu objekt
        $data = (object) $this->data;
    
        include( $this->viewfile );
    }
    
    /**
     * Generovanie náhodného Captcha kódu
     * 
     * Metóda je potrebná aby bolo možné generovať obrázok s captcha kódom cez atribút src="" a URL adresu (page/captcha)
     */
    public function captcha(){
                
        Mcore::base()->storeObject( 'captcha', 'captcha');
        Mcore::base()->getObject('captcha')->generateCaptcha();
    }
    
    /**
     * Vytvori zaznam o navsteve stranky
     * @since 1.2 Cute Genie
     */
    public function traffic(){
        
        Mcore::base()->storeObject( 'traffic', 'traffic');
        $model = Mcore::base()->getObject('traffic')->model();
        $model->address_ip = $_SERVER['REMOTE_ADDR'];
        if( !empty( $_POST['u'] ) ) {
            $model->url = $_POST['u'];
        }
        else{
            $model->url = 'not provided';
        }
        if( !empty( $_POST['r'] ) ) {
            $model->referer = $_POST['r'];
            $model->referer_domain = array_shift( explode( '/', str_replace( array( 'http://','https://' ), '', $model->referer) ) );
        }
        $model->save();
    }
    
    /**
     * Metóda uloží do cache súboru vonkajšiu, statickú, časť HTML kódu
     * Pokiaľ vytvorenie tejto časti obsahuje viacero SQL dotazov, nemusia sa vykonávať
     * čím sa zvýši rýchlosť načítania stránky
     * @param String Názov šablony
     * @param String Názov šablony
     */
    protected function cacheTemplate( $output, $template ){
        
        if( Mcore::base()->getSetting( 'MCORE_CACHETEMPLATE_USE' ) ){
            //file_put_contents( MCORE_APP_PATH . "cache/CACHE_TEMPLATE__{$template}.html", preg_replace( '/\r\n|\n|\r|\s{2,}|\t/', '', $output ) );
            file_put_contents( Mcore::mPathResolver( 'cache' ) . "cache/CACHE_TEMPLATE__{$template}.html", $output );
        }
    }
    
    /**
     * Získa vonkajšiu šablónu z cache
     * Ak sablona existuje v cache priečinku a jej dátum vytvorenia nebol dávnejšie ako MCORE_CACHETEMPLATE_REFRESH
     * @param String Názov šablony
     * @return String | FALSE
     */
    protected function getCacheTemplate( $template ){
        
        $filename = Mcore::mPathResolver( 'cache' ) . "cache/CACHE_TEMPLATE__{$template}.html";
        if( file_exists( $filename ) ){
            if( Mcore::base()->getSetting( 'MCORE_CACHETEMPLATE_REFRESH' ) === FALSE || 
                    (  time() - filemtime( $filename ) < Mcore::base()->getSetting( 'MCORE_CACHETEMPLATE_REFRESH' ) ) ){
                ob_start();
                include $filename;
                return ob_get_clean();
            }
            else{
                return FALSE;
            }
        }
        else{
           return FALSE; 
        }
    }
    
    /**
     * Metoda vráti string pripraveny podľa $_GET parametrov URL adresy umožňujúci zoradzovanie v SQL dotaze
     * @param array $enableSort asociativne pole 'nazov_stlpca_db' => 'parameter_get'
     * @return String Podmienka zacinajuca s ORDER BY databazovy dotaz
     */
    protected function getSortCondition( $enableSort ){
        $sort = "ORDER BY t.id DESC";
        if( !empty( $_GET['sort'] ) ){
            if ( ( $column = array_search( $_GET['sort'], $enableSort ) ) !== FALSE ){
                $sort = "ORDER BY {$column}";
                if( !empty( $_GET['order'] ) 
                        && ( strtolower($_GET['order']) == 'asc' || strtolower($_GET['order']) == 'desc' ) ){
                    $sort .= " {$_GET['order']}";
                }
            }
        }
        return $sort;
    }
}