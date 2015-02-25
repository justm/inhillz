<?php
/**
 * Súbor obsahuje triedu ClientScript, ktorá umožňuje vložiť CSS alebo javascript obsah do hlavičky stránky
 *
 * @author Matus Macak < matus.macak@folcon.sk > 
 * @link http://www.folcon.sk/
 * @version 1.1 Mercury drop
 * @since Subor je súčasťou aplikácie od verzie 1.1
 * @package mcore.mcore\.base.objects
 *
 */

class Cachescript {
    
    /**
     * @var int $CFLUSH_HEAD Konštanta pre umiestnenie kódu do hlavičky HTML dokumentu
     */
    const CFLUSH_HEAD = 0;
    
    /**
     * @var int $CFLUSH_BODYOPEN Konštanta pre umiestnenie kódu za otvárací <body> tag
     */
    const CFLUSH_BODYOPEN = 1;
    
    /**
     * @var int $CFLUSH_BODYEND Konštanta pre umiestnenie kódu na záver <body> elementu
     */
    const CFLUSH_BODYEND = 2;
            
    /**
     * @var array Pole s cestami k javascriptovým súborom, ktoré maju byt nalinkovane
     */
    protected $javascriptsFiles = array();
    
    /**
     * @var array Pole s cestami css suborov, ktore maju byt nalinkovane
     */
    protected $cssFiles = array();
    
    /**
     * @var array Pole s časťami kódu ktoré majú byť vložené do stránky pri jej vytvorení
     *            Štruktúra: 'key' => array( const POSITION, String SNIPPET );
     */
    protected $codeSnippets = array();
    
    /**
     * Zaregistruje javascriptový súbor
     * @param string $fileurl Url adresa súboru
     * @param string $key [optional] Kľúč pod ktorým má byť uložený, 
     *        ak je registrovaný script dva krát pod tým istý kľúčom, novší prepíše starší
     */
    public function registerJavascriptFile( $fileurl, $key = ''){
        
        if( empty($key) ){
            $key = count($this->javascriptsFiles);
        }
        $this->javascriptsFiles[$key] = $fileurl; 
    }
    
    /**
     * Zaregistruje CSS súbor
     * @param string $fileurl Url adresa súboru
     * @param string $key [optional] Kľúč pod ktorým má byť uložený,
     *        ak je registrovaný súbor dva krát pod tým istý kľúčom, novší prepíše starší
     */
    public function registerCssFile( $fileurl, $key = ''){
        
        if( empty($key) ){
            $key = count($this->javascriptsFiles);
        }
        $this->cssFiles[$key] = $fileurl; 
    }
    
    /**
     * Zaregistruje CSS súbor
     * @param string $fileurl Url adresa súboru
     * @param string $key [optional] Kľúč pod ktorým má byť uložený,
     *        ak je registrovaný súbor dva krát pod tým istý kľúčom, novší prepíše starší
     */
    public function registerCodeSnippet( $codeSnippet, $key = '', $position = self::CFLUSH_BODYOPEN ){
        
        if( empty($key) ){
            $key = count($this->codeSnippets);
        }
        $this->codeSnippets[$key] = array( $position, $codeSnippet ); 
    }
    
    /**
     * Vráti HTML kód, ktorý po pridaní do stránky pripojí registrované súbory
     * @param String $cachedHTML HTML kód s hlavičkou a telom
     * @param array $whichones [optional] Je možné definovať, ktoré súbory majú byť pri volaní tejto funkcie vložené do HTML kódu stránky
     * @return string HTML snippet
     */
    public function flushFiles( $cachedHTML, $whichones = array() ){
        
        $snippet = ''; 
        if( empty( $whichones ) ){
            $whichones = array_merge( array_keys( $this->cssFiles ), array_keys( $this->javascriptsFiles ) ) ;
        }
        
        foreach ( $whichones as $key ){
            
            if( array_key_exists( $key, $this->javascriptsFiles ) ){
                
                $snippet .= '<script type="text/javascript" src="' . $this->javascriptsFiles[$key] . '"></script>';
            }
            elseif( array_key_exists( $key, $this->cssFiles ) ) {
                $snippet .= '<link rel="stylesheet" href="' . $this->cssFiles[$key] . '">';
            }
        }
        
        return substr_replace( $cachedHTML, $snippet, strpos( $cachedHTML, '</head>' ), 0 );
    }
    
    /**
     * Vloží prepripravený HTML kód do HTML stránky podľa definovej pozície
     * @param String $cachedHTML HTML stránka do ktorej bude kód vložený
     * @param array $whichones [optional] Je možné definovať, ktoré súbory majú byť pri volaní tejto funkcie vložené do HTML kódu stránky
     * @return string HTML snippet
     */
    public function flushCache( $cachedHTML, $whichones = array() ){
        
        if( empty( $whichones ) ){
            $whichones = array_keys( $this->codeSnippets );
        }
        
        foreach ( $whichones as $key ){
            
            if( array_key_exists( $key, $this->codeSnippets ) ){
                
                switch ( array_shift($this->codeSnippets[$key]) ){
                    case self::CFLUSH_HEAD:
                        $pos = strpos( $cachedHTML, '</head>' );
                        break;
                    
                    case self::CFLUSH_BODYEND:
                        $pos = strpos( $cachedHTML, '</body>' );
                        break;
                    
                    case self::CFLUSH_BODYOPEN:
                    default:
                        $pos = strpos( $cachedHTML, '<body>' ) + 6;
                        break;
                }
            }
            $cachedHTML = substr_replace( $cachedHTML, array_shift($this->codeSnippets[$key]), $pos, 0 );
        }
        
        return $cachedHTML;
    }
}