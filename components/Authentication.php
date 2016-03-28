<?php

namespace inhillz\components;

use orchidphp\Orchid;

/**
 * Extends framework authentication class. Represents logged user session.
 * 
 * @package    inhillz\components
 * @author     Matus Macak <matus.macak@orchidsphere.com>
 * @link       http://ride.inhillz.com/
 * @version    2.0
 */
class Authentication extends \orchidphp\Authentication{
        
    /**
     * First name
     * @var string
     */
    protected $name;
    
    /**
     * Name and lastname
     * @var string
     */
    protected $fullname;
    
    /**
     * Session expiration in seconds, 1 week
     * @var type 
     */
    protected $session_expiration = 2592000;
        
    /**
     * Returns logged user name
     * @return String
     */
    public function getName() {
        return $this->name;
    }
    
    /**
     * Returns logged user name and lastname
     * @return String
     */
    public function getFullname() {
        return $this->fullname;
    }
    
    /**
     * @inheritdoc
     */
    protected function sessionAuthenticate($uid) {
        
        $sql = "SELECT u.ID as id, u.username, u.email, u.name, u.lastname, r.name as role_name
                FROM user u LEFT JOIN `role` r on u.id_role = r.id WHERE u.ID = {$uid}";

        Orchid::base()->getObject('db')->executeQuery( $sql ); 
        
        //If user exists
        if(Orchid::base()->getObject('db')->getNumRows() == 1){
            $userData = Orchid::base()->getObject('db')->getObjectRows()[0];
            
            $this->updateSession($userData->id);

            $this->role     = $userData->role_name;
            $this->username = $userData->username;
            $this->name     = $userData->name;
            $this->fullname = $userData->name . ' ' . $userData->lastname;
        }
        else {
            $this->loggedIn = false;
            $this->loginFailureReason = self::NOUSER;
        }
        
        if( $this->loggedIn == false ) {
            $this->logout();
        } 
    }
    
    /**
     * @param string $e email
     * @param string $p password
     */
    public function postAuthenticate($e, $p) {
        
        echo $sql = "SELECT u.ID as id, u.username, u.email, u.name, u.lastname, r.name as role_name
                FROM user u LEFT JOIN `role` r on u.id_role = r.id WHERE
                u.email = '{$e}' AND u.password = '" . $this->getHash($p) . "'";

        Orchid::base()->getObject('db')->executeQuery($sql); 
        
        //If user exists
        if(Orchid::base()->getObject('db')->getNumRows() == 1){
            $userData = Orchid::base()->getObject('db')->getObjectRows()[0];
            
            $this->updateSession($userData->id);

            $this->role     = $userData->role_name;
            $this->username = $userData->username;
            $this->name     = $userData->name;
            $this->fullname = $userData->name . ' ' . $userData->lastname;
       }
       else {
           $this->loggedIn = false;
           $this->loginFailureReason = self::INVALID_CREDENTIALS;
       }
    }
    
    /**
     * @param string $e email
     * @param string $t oAuth token
     */
    public function oAuthenticate($e, $t) {
        
        $sql = "SELECT u.ID as id, u.username, u.email, u.name, u.lastname, r.name as role_name
                FROM user u LEFT JOIN `role` r on u.id_role = r.id WHERE
                u.email = '{$e}' AND u.access_token = '{$t}' AND u.token_expires > UNIX_TIMESTAMP(NOW())"; 
        Orchid::base()->getObject('db')->executeQuery($sql); 
        
        //If user exists
        if(Orchid::base()->getObject('db')->getNumRows() == 1){
            $userData = Orchid::base()->getObject('db')->getObjectRows()[0];

            $this->updateSession($userData->id);

            $this->isAdmin = ($userData->admin == 1)? true : false;
            $this->role    = $userData->role_name;

            $this->username = $userData->username;
            $this->name     = $userData->name;
            $this->fullname = $userData->name . ' ' . $userData->lastname;
       }
       else {
           $this->loggedIn = false;
           $this->loginFailureReason = self::INVALID_CREDENTIALS;
       }
    }
    
    /**
     * @inheritdoc
     */
    public function getHash($password) {
        return md5("pinarello" . $password);
    }
}
