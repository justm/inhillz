<?php
/**
 * Súbor obsahuje triedu UserController
 *
 * @author Matus Macak < matus.macak@folcon.sk > 
 * @link http://www.folcon.sk/
 * @version 2.0
 * @since Subor je súčasťou aplikácie od verzie 2.0
 * @package controllers
 * 
 */

/**
 * Ovládač UserController 
 */
class UserController extends McontrollerCore{
    
    /**
     * Metóda definuje prístupové práva k metódam. Každá metóda, ktorá má prejsť kontrolou práv
     * musí byť v rámci triedy definovaná ako protected
     * 
     * @return array
     */
    public function accessRules() {
        
        return array(
            
        );
    }
        
    /**
     * Prihlásenie používateľa
     */
    public function login() {
              
        $registry = Mcore::base();
        $user     = new UserModel();

        if( isset( $_POST['UserModel'] ) ){
            $registry->authenticate->postAuthenticate( $_POST['UserModel']['email'], $_POST['UserModel']['password'] );

            if( $registry->authenticate->isLoggedIn() ){ 
                Mcore::base()->urlresolver->redirectBack();
            }
            else{ //Neprihlásený znovu načítaj formulár so zobrazenými hláseniami
                time_nanosleep( 2, 0 );
                $registry->userflash->setFlash( 
                        'not-logged-in','alert alert-warning', $registry->authenticate->getTextLoginFailureReason() );

                $this->render( 'login', array( 'user'=> $user ) );
            }
        }
        else{
            $this->render( 'login', array( 'user'=> $user ) );
        }
    }
    
    public function logout(){
        
        Mcore::base()->authenticate->logout();
        Mcore::base()->urlresolver->redirect("");
    }
}