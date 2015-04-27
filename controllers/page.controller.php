<?php
/**
 * Súbor obsahuje triedu PageController
 *
 * @author Matus Macak < matus.macak@folcon.sk > 
 * @link http://www.folcon.sk/
 * @version 2.0
 * @since Subor je súčasťou aplikácie od verzie 2.0
 * @package controllers
 * 
 */

/**
 * Ovládač PageController 
 */
class PageController extends McontrollerCore{
    
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
    
    public static function controller(){
        
        return new PageController();
    }
    
    /**
     * Zobrazenie hlavnej stránky, HOMEPAGE
     */
    public function index() {        
        
        $this->render('index');
    }
    
    /**
     * Metóda pre spracovanie a zobrazenie errorov aplikácie
     * @param int $code Kód chyby
     * @param string $flash Správa zobrazená používateľovi
     * @param boolean $showPlain Rozhodne či sa zobrazí aj vonkajší kontajner
     */
    public function error( $code, $flash = '', $showPlain = FALSE ){
        
        $message = Mcore::base()->getObject('urlresolver')->getHeaderPhrase( $code );
        header("HTTP/1.0 {$message}");
        
        if( $showPlain ){
            $this->template = 'main.plain';
            $this->render( 'error', array( 'code'=> $code, 'flash' => $flash ) );
        }
        else{
            $this->render( 'error', array( 'code'=> $code, 'flash' => $flash ) );
        }
        exit(1);
    }
    
    /**
     * Metóda pre odoslanie dotazu na produkt alebo iných otázok od používateľa
     */
    public function contact() {
        
        $success = FALSE;
        $captchaValid = TRUE;
        $user_id = Mcore::base()->getObject('authenticate')->getUserID();
        if( ( $user = UserModel::model()->findById( $user_id ) ) != NULL ){
            $contact = new ContactModel( $user->name, $user->email );
        }else{
            $contact = new ContactModel();
        }
        
        $settings = Mcore::base()->getSetting('globalParams');
        if( isset( $_POST['ContactModel'] ) ){
            
            if( Mcore::base()->getObject('captcha')->requiredCaptcha() ){
                if( isset( $_POST['captcha'] )
                    && Mcore::base()->getObject('captcha')->checkCaptcha( $_POST['captcha'] ) ){
                    $captchaValid = TRUE;
                }
                else{
                    $contact->setValidationErrors('<li>' . Mcore::t('You have not entered the correct validation code from the picture'). '</li>');
                    $captchaValid = FALSE;
                }
            }
            
            $contact->setAttributes( $_POST['ContactModel'] );

            if( $contact->validate() ){ //Validácia a odoslanie

                if( Mcore::base()->getObject('captcha')->requiredCaptcha() ){
                    if( isset( $_POST['captcha'] )
                        && Mcore::base()->getObject('captcha')->checkCaptcha( $_POST['captcha'] ) ){
                        $captchaValid = TRUE;
                    }
                    else{
                        $contact->setValidationErrors('<li>' . Mcore::t('You have not entered the correct validation code from the picture'). '</li>');
                        $captchaValid = FALSE;
                    }
                }
                if( $captchaValid ){
                    $name='=?UTF-8?B?'.base64_encode($contact->name).'?=';
                    $subject='=?UTF-8?B?'.base64_encode($contact->subject).'?=';
                    $headers="From: $name <{$contact->email}>\r\n".
                            "Reply-To: {$contact->email}\r\n".
                            "MIME-Version: 1.0\r\n".
                            "Content-type: text/plain; charset=UTF-8";

                    mail( $settings['contactemail'] , $subject, $contact->bodyText, $headers );
                    $success = TRUE;
                }
            }
            
            if( isset( $_POST['ajaxForm'] ) ){
                $this->renderPartial( 'contact.form', array( 'contact' => $contact, 'success' => $success ), 'contact' );
            }else{
                $this->render( 'contact', array( 'contact' => $contact, 'success' => $success ), 'contact' );
            }
        }else{
            $this->render( 'contact', array( 'contact' => $contact, 'success' => $success ), 'contact' );
        }
    }
}
