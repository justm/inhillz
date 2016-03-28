<?php
/**
 * Súbor obsahuje triedu UserModel, ktorá predstavuje model pre DB tabuľky USER
 *
 * @author Matus Macak < matus.macak@folcon.sk > 
 * @link http://www.folcon.sk/
 * @version 1.1 Mercury drop
 * @since Subor je súčasťou aplikácie od verzie 1.0
 * @package models
 * 
 */

/**
 * Trieda UserModel predstavuje model pre DB tabuľku USER, ktorá uchováva údaje o 
 * registrovaných používateľoch
 *  @property int id_role
 *  @property int id_address
 *  @property int id_primary_activity
 *  @property int id_user_fitness
 *  @property int id_privacy
 *  @property String name
 *  @property String lastname
 *  @property String fullname
 *  @property String username
 *  @property String password
 *  @property String email
 *  @property String gender
 *  @property String birthday
 *  @property String phone
 *  @property String about
 *  @property String profile_picture
 *  @property String account_status
 */

    

class UserModel extends MmodelCore{
    
    /**
     * Virtuálny atribút heslo. Použije sa vo formulári
     */
    public $password = '';
    
    /**
     * Virtuálny atribút zopakovanie hesla. Použije sa vo formulári
     */
    public $repeatPassword = '';
    
    /**
     * Virtuálny atribút staré heslo. Použije sa vo formulári na zmenu hesla
     */
    public $oldPassword = '';
    
    /**
     * Virtuálny atribút nové heslo. Použije sa vo formulári na zmenu hesla, iba pre label
     */
    public $newPassword = '';
    
    /**
     * Metóda vráti statický model špecifikovanej triedy
     * @param string Názov triedy
     * @return UserModel
     */
    public static function model( $className = __CLASS__ ){
        return parent::model( $className );
    }
    
    /**
     * Metóda vráti názov tabuľky v DB, ku ktorej prislúcha tento model
     * @return String Názov tabuľky
     */
    public function table(){
        return 'user';
    }
    
    /** 
     * Metóda, ktorá vráti pole definovaných označení pre jednotlivé atribúty modelu
     * @return array
     */
    public function labels() {
        
        return array(
            'email' => Mcore::t('Email','user'),
            'password' => Mcore::t('Password','user'),
        );
    }

    /**
     * Metóda, ktorá vráti pole definovaných pravidiel pre jednotlivé atribúty modelu
     * @return array
     */
    public function rules() {
        
        return array(
            'required' => ' name, surname, email, phonenumber, passwordhash, password, repeatPassword,',
            'length' => array( '20'=> ' phonenumber,', '50' => ' name, surname, email,'),
            'unique' => ' email,',
            'format' => array( 'email' => ' email,'), //format => attribute
        );
    }
    
    public function findById($id, $columns = '*', $joins = '') {
        return parent::findById(
                $id, 
                'f.weight, f.height, f.max_hr, f.rest_hr, t.*', 
                'LEFT JOIN user_fitness f on t.id_user_fitness = f.id' 
        );
    }
} 