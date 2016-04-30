<?php

namespace inhillz\controllers;

use inhillz\models\UserModel;
use orchidphp\Orchid;

/**
 * Súbor obsahuje ovládač UserController
 *
 * @package    inhillz\controllers
 * @author     Matus Macak <matus.macak@orchidsphere.com>
 * @link       http://ride.inhillz.com/
 * @version    2.0
 * 
 */
class UserController extends AbstractWebController{
    
    /**
     * @inheritdoc
     */
    public function accessRules() {
        
        return array(
            
        );
    }
        
    /**
     * Prihlásenie používateľa
     */
    public function login() {
              
        $user = new UserModel();

        if( isset( $_POST['UserModel'] ) ){
            Orchid::base()->authenticate->postAuthenticate( $_POST['UserModel']['email'], $_POST['UserModel']['password'] );

            if( Orchid::base()->authenticate->isLoggedIn() ){ 
                Orchid::base()->urlresolver->redirectBack();
            }
            else{ //Neprihlásený znovu načítaj formulár so zobrazenými hláseniami
                time_nanosleep( 2, 0 );
                Orchid::base()->userflash->setFlash( 
                        'not-logged-in','alert alert-warning', Orchid::base()->authenticate->getTextLoginFailureReason() );

                $this->render( 'login', array( 'user'=> $user ) );
            }
        }
        else{
            $this->render( 'login', array( 'user'=> $user ) );
        }
    }
    
    public function logout(){
        
        Orchid::base()->authenticate->logout();
        Orchid::base()->urlresolver->redirect("");
    }
}