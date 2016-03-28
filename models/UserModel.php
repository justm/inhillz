<?php

namespace inhillz\models;

use orchidphp\Orchid;

/**
 * Trieda UserModel
 * Model pre DB tabuľku USER, ktorá uchováva údaje o registrovaných používateľoch
 * 
 * @package    inhillz\components
 * @author     Matus Macak <matus.macak@orchidsphere.com>
 * @link       http://ride.inhillz.com/
 * @version    2.0
 * 
 * @property int id_role
 * @property int id_address
 * @property int id_primary_activity
 * @property int id_user_fitness
 * @property int id_privacy
 * @property String name
 * @property String lastname
 * @property String fullname
 * @property String username
 * @property String password
 * @property String email
 * @property String gender
 * @property String birthday
 * @property String phone
 * @property String about
 * @property String profile_picture
 * @property String account_status
 */
class UserModel extends \orchidphp\AbstractModel{
    
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
     * @inheridoc
     */
    public static function model( $className = __CLASS__ ){
        return parent::model( $className );
    }
    
    /**
     * @inheridoc
     */
    public function table(){
        return 'user';
    }
    
    /** 
     * @inheridoc
     */
    public function labels() {
        
        return array(
            'email' => Orchid::t('Email','user'),
            'password' => Orchid::t('Password','user'),
        );
    }

    /**
     * @inheridoc
     */
    public function rules() {
        
        return array(
            'required' => ' name, surname, email, phonenumber, passwordhash, password, repeatPassword,',
            'length' => array( '20'=> ' phonenumber,', '50' => ' name, surname, email,'),
            'unique' => ' email,',
            'format' => array( 'email' => ' email,'), //format => attribute
        );
    }

    /**
     * @inheridoc
     */
    public function findById($id, $columns = '*', $joins = '') {
        return parent::findById(
                $id, 
                'f.weight, f.height, f.max_hr, f.rest_hr, t.*', 
                'LEFT JOIN user_fitness f on t.id_user_fitness = f.id' 
        );
    }
} 