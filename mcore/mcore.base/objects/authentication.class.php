<?php
/**
 * Súbor obsahuje triedu Authentication
 *
 * @author Matus Macak < matus.macak@folcon.sk > 
 * @link http://www.folcon.sk/
 * @version 1.1 Mercury drop
 * @since Subor je súčasťou aplikácie od verzie 1.0
 * @package mcore.mcore\.base.objects
 *
 */

/**
 * Trieda Authentication slúži na autentifikáciu a prihlásenie používateľa
 */
class Authentication {
     
    /**
     * @var int ID používateľa
     */
    private $userID = 0;
    
    /**
     * @var bool Definuje či je používateľ prihlásený
     */
    private $loggedIn = false;
    
    /**
     * @var bool Definuje či je používateľ administrátorom
     */
    private $admin = false;
    
    /**
     * @var String Typ používateľskej role, kvôli funkciam s definovanými prístupovými právami
     */
    public $role = '';
    
    /**
     * @var bool Definuje či je povolené používanie používateľkého účtu
     */
    private $banned = false;
    
    /**
     * @var String Používateľské meno
     */
    private $username;
    
    /**
     * @var String Meno
     */
    private $name;
    /**
     * @var String Celé meno aj s priezviskom
     */
    private $fullname;
    
    /**
     * @var bool Definuje či sa používateľ práve prihlásil
     */
    private $justProcessed = false;
    
    /**
     * @var String
     */
    private $loginFailureReason;
    
    /**
     * @var bool
     */
    private $active;
    
    /**
     * Konštruktor autentifikácie
     */
    public function __construct() { }
    
    /**
     * Metóda zaisťuje autentifikáciu používateľa
     */
    public function checkForAuthentication() {
        
        if( isset( $_SESSION['magnecomf_session_uid'] ) && 
                intval( $_SESSION['magnecomf_session_uid'] ) > 0 ) {
            
            $this->sessionAuthenticate( intval( $_SESSION['magnecomf_session_uid'] ));
        }
    }
    
    /**
     * Vyhľadanie používateľa na základe ID z relácie a nastavenie parametrov pre prihlásenie
     * @param int $uid
     */
    private function sessionAuthenticate( $uid ) {
        
        $sql = "SELECT u.ID, u.username, u.email, u.name, u.lastname, r.name as role_name
                FROM user u LEFT JOIN `role` r on u.id_role = r.id WHERE u.ID = {$uid}";
        Mcore::base()->getObject('db')->executeQuery( $sql ); 
        
        //Ak používatel existuje
        if( Mcore::base()->getObject('db')->getNumRows() == 1 ){
            $userData = Mcore::base()->getObject('db')->getArrayRows();
            if( $userData[0]['banned'] != 0 ) {
                $this->loggedIn = false;
                $this->loginFailureReason = 'banned';
                $this->banned = true;
            }
            else {
                $this->loggedIn = true;
                $this->userID = $uid;
                
                $this->admin = ( $userData[0]['admin'] == 1 )? true : false;
                $this->role = $userData[0]['role_name'];
                
                $this->username = $userData[0]['username'];
                $this->name = $userData[0]['name'];
                $this->fullname = $userData[0]['name'] . ' ' . $userData[0]['lastname'];
            }
        }
        else {
            $this->loggedIn = false;
            $this->loginFailureReason = 'nouser';
        }
        
        if( $this->loggedIn == false ) {
            $this->logout();
        } 
    }
    
    /**
     * Vyhľadanie používateľa na základe zadaného mena a hesla a nastavenie parametrov pre prihlásenie
     * @param String $e Email
     * @param String $p Heslo
     */
    public function postAuthenticate( $e, $p ) {
        
        $this->justProcessed = true;
        $sql = "SELECT u.ID, u.username, u.email, u.name, u.lastname, r.name as role_name
                FROM user u LEFT JOIN `role` r on u.id_role = r.id WHERE
                u.email = '{$e}' AND u.password = '" . $this->getHash($p) . "'"; 
        Mcore::base()->getObject('db')->executeQuery( $sql ); 
        
        //Ak používatel existuje
        if( Mcore::base()->getObject('db')->getNumRows() == 1 ){
            $userData = Mcore::base()->getObject('db')->getArrayRows();
            if( $userData[0]['banned'] != 0 ) {
                $this->loggedIn = false;
                $this->loginFailureReason = 'banned';
                $this->banned = true;
            }
            else {
                $this->loggedIn = true;
                $this->userID = $_SESSION['magnecomf_session_uid'] = $userData[0]['ID'];
                
                $this->admin = ( $userData[0]['admin'] == 1 )? true : false;
                $this->role = $userData[0]['role_name'];
                
                $this->name = $userData[0]['name'];
                $this->fullname = $userData[0]['name'] . ' ' . $userData[0]['lastname'];
            }
       }
       else {
           $this->loggedIn = false;
           $this->loginFailureReason = 'invalidCredentials';
       }
    }
    
    /**
     * Metóda vráti pole s prekladom kódov o neúspešnom prihlásení
     * @return array Vo formáte 'kod' => 'Sprava'
     */
    public function failureCodesTranslate(){
        
        return array(
            'invalidCredentials' => Mcore::t('Invalid credentials.'),
            'banned' => Mcore::t('Your account is banned.'),
            'inactive' => Mcore::t('Your account is no longer active'),
            'nouser' => Mcore::t('Sorry, you are not logged in.'),
        );
    }
    
    /**
     * Odhlásenie používateľa odobratím identifikátoru z relácie
     */
    public function logout() {
        $_SESSION['magnecomf_session_uid'] = '';
    }
    
    /**
     * Metóda vráti ID prihláseného používateľa
     * @return int
     */
    public function getUserID() {
        return $this->userID;
    }

    /**
     * Metóda vráti používateľské meno prihláseného používateľa
     * @return String
     */
    public function getUsername() {
        return $this->username;
    }
    
    /**
     * Metóda vráti meno prihláseného používateľa
     * @return String
     */
    public function getName() {
        return $this->name;
    }
    
    /**
     * Metóda vráti celé meno aj s priezviskom prihláseného používateľa
     * @return String
     */
    public function getFullname() {
        return $this->fullname;
    }
    
    /**
     * Vráti príčinu neúspešneho prihlásenia
     */
    public function getLoginFailureReason() {
        return $this->loginFailureReason;
    }
    
    /**
     * Vráti príčinu neúspešneho prihlásenia
     * @return String ak existuje správa pre požadovaný kód vráti sa správa inak sa vráti len textová skratka
     */
    public function getTextLoginFailureReason() {
        
        $err = $this->failureCodesTranslate();
        if( array_key_exists( $this->loginFailureReason, $err ) ){
            return $err[$this->loginFailureReason];
        }else{
            return $this->loginFailureReason;
        }
    }

        /**
     * Metóda vráti logickú hodnotu, ktorá definuje, či je používateľ prihlásený
     * @return bool
     */
    public function isLoggedIn() {
        return $this->loggedIn;
    }
    
    /**
     * Metóda vráti logickú hodnotu, ktorá definuje, či má používateľ administrátorské práva
     * @return bool
     */
    public function isAdmin() {
        return $this->admin;
    }
    
    /**
     * Metoda na zahashovanie hesla s pridaním salt
     * 
     * @param string Nezahashované heslo
     * @return string Zahashované heslo
     */
    public function getHash($password) {
            return md5("pinarello" . $password);
    }
}