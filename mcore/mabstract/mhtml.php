<?php
/**
 * Súbor obsahuje triedu MhtmlCore
 *
 * @author Matus Macak < matus.macak@folcon.sk > 
 * @link http://www.folcon.sk/
 * @version 1.1 Mercury drop
 * @since Subor je súčasťou aplikácie od verzie 1.0
 * @package mcore.mabstract
 * 
 */

/**
 * Trieda MhtmlCore slúži k zautomatizovaniu vytvárania HTML elementov
 */
class MhtmlCore {
    
    /**
     * Konštruktor triedy 
     */
    public function __construct() {}

    /**
     * Statická metóda, ktorá vytvorí začiatočný HTML tag pre formulár
     * @param String $action
     * @param String $method [optional] Ak nie je špecifikovaná použije sa GET
     * @param array  $options [optional]
     * @return String HTML začiatočný tag
     */
    public static function beginForm( $action, $method='GET', $options = array() ){
        
        $form = '<form action="' . $action . '" ';
        $form .= 'method="' . $method . '" ';
        foreach ( $options as $attrib => $val ) {
            $form .= $attrib . '="' . $val . '" ';
        } 
        $form .= ">";
        return $form;
    }
    
    /**
     * Statická metóda, ktorá vráti ukončujúci HTML tag pre formulár
     * @return String HTML ukončujúci tag
     */
    public static function endForm( ){
        
        return '</form>';
    }
    
    /**
     * Metóda vypíše zoznam errorov pre vstupy z formulára. Napr. nevyplnené povinné položky
     * a nesprávny formát vstupu
     * @return String HTML snippet
     */
    public static function displayErrors( $model ){
        
        $errors = $model->getValidationErrors();
        $ret = '';
        if( $errors != '' ){
            $ret .= '<div class="error-flash alert alert-warning"><ul>';
                $ret .= $errors;
            $ret .= '</ul></div>';
        }
        return $ret;
    }
    
    /**
     * Metóda vypíše oznámenia z fronty USERFLASH. 
     * Štandardne sa používa na výpis oznámení o úspešnosti operácií 
     * @return String HTML snippet
     */
    public static function displayFlash( ){
        
        $snippet = '';
        $userflashHandler = Mcore::base()->getObject('userflash');
        
        $flashes = $userflashHandler->getFlashes();
        if( !empty( $flashes ) ){
           
           foreach ( $flashes as $flash ){
               $snippet = '<div class="'.$flash->type.'">';
               $snippet .= $flash->text;
               $snippet .= '</div>';
           }
           
        }
        return $snippet;
    }
    
    /**
     * Vytvorí panel s možnosťou prepínania jazyka pre úpravu dát
     * @since 1.1 Mercury drop
     * @param array $options HTML atribúty pre výsledný element
     * @return String HTML snippet
     */
    public static function languageBar( $options = array() ){
        
        $langs = Mcore::base()->getObject('shopid')->getAllShops();
        if( count($langs) < 2 ){
            return NULL;
        }
        
        $href = 'http://' . $_SERVER['HTTP_HOST'] . preg_replace('/[\?&]?datacode=\w+&?/', '', $_SERVER['REQUEST_URI']);
        
        $queryStr = preg_replace('/[\?&]?datacode=\w+&?/', '', $_SERVER['QUERY_STRING']);
        $href .= empty( $queryStr )? '?' : "&";
        
        $bar = '<div ';
        $bar.= 'class="language-bar ';
        if( array_key_exists( 'class', $options ) ) {
            $bar .= $options['class'] . '" ';
            unset($options['class']);
        }else{
            $bar .= '" ';
        }
        foreach ( $options as $attrib => $val ) {
            $bar .= $attrib . '="' . $val . '" ';
        }
        $bar .= '>';
        $getDataCode = isset( $_GET['datecode'] )? $_GET['datacode'] : '';
        foreach ( $langs as $code => $lang ){
            $bar .= '<a href="' . $href . 'datacode=' . $code .'" ' 
                 . (($getDataCode == $code || $code == Mcore::base()->getObject('shopid')->getCountryCode())? 'class="langActive"' : "")
                 . '>'.$lang
                 . '</a>';
        }
        $bar .= '<div style="clear: both"></div>';
        $bar .= '</div>';
        
        return $bar;
    }
    
    /**
     * Vytvorí hlavičku tabuľky s možnosťou zoradzovania kliknutím na záhlavie
     * @since 1.1 Mecury drop
     * @param array $columns Pole, ktore obsahuje dalsie polia s nasledovnymi atributmi:
     *          nazov stlpca aky sa ma zobrazit na stranke, 
     *          nazov stlpca(realny) podla databazy, pouzije sa k vytvoreniu linku aby bolo mozne poziadavku jednoducho spracovat v ovladaci     *          
     *          - v pripade ze je nazov stlpca prazdny retaze, stlpec nebude klikatelny
     *          a asociativne pole s nastaveniami - array( 'style'=>'color: black', 'colspan' => '2', ... )
     *          
     * @return String HTML snippet
     */
    public static function activeThead( $columns ){
        
        $ret = '<thead><tr>';
        foreach ( $columns as $column ){
            
            $getSort    = (array) $_GET['sort'];
            $getOrder   = (array) $_GET['order'];
            $node_class = '';
            $ret       .= '<th ';
            
            if( is_array( $options = array_pop( array_values( $column ) ) ) ) {
                foreach ( $options as $attribute => $value ){
                    $ret .= $attribute . '="' . $value . '" ';
                }
            }
            $ret .= '>';

            $displayName = array_shift( $column );
            $colName     = array_shift( $column );

            if( !empty( $colName ) ){
                if( ($pos = array_search($colName, $getSort)) !== FALSE ){

                    if( !empty($getOrder[$pos]) && $getOrder[$pos] == strtolower('asc') ){
                        $getOrder[$pos] = 'desc'; // this is next value after clicking the link
                        $node_class     = 'oASC'; // this is current value for CSS
                    }
                    else{
                        unset($getSort[$pos]);
                        unset($getOrder[$pos]);
                        $node_class     = 'oDESC';
                    }
                }
                else{
                    $getSort[]  = $colName;
                    $getOrder[] = 'asc';
                }
                
                $query_string = self::activeTheadQstring($getSort, $getOrder);
                
                $ret .= '<a href="' . $query_string . '" class="activeTHlink '; 
                $ret .= $node_class .'"';
                $ret .= '>' . $displayName . '</a>';
            } 
            else{
                $ret .= $displayName;
            }
            $ret .= '</th>';
        }
        $ret .= '</tr></thead>';
        return $ret;
    }

    /**
     * Array map pre vytvorenie query string vo funkcii activeThead()
     */
    protected static function activeTheadQstring( $sorts, $orders ){
        
        $q = '?';
        foreach ( $sorts as $key => $sort ){
            $q .= "sort[]={$sort}&order[]={$orders[$key]}&";
        }
        return $q;
    }

    /**
     * Statická metóda, ktorá vytvorí HTML LABEL tag
     * @param object $model
     * @param String $attribute
     * @return String vytvorený HTML LABEL element
     */
    public static function mLabel( $model, $attribute, $inputID = '', $options = array() ) {
        
        $lbl = '';
        
        if( method_exists($model, 'getTags') ){
            $lbl = $model->getTags( $attribute )['label']; 
        }
        if( empty($lbl) ){
            $lbl = $model->labels()[$attribute];
        }
        
        $attrsRules = $model->rules();
                
        if( $inputID == '' ) {
            $label = '<label for="' . get_class($model) . '_'.$attribute . '" ';
        }
        else{
            $label = '<label for="' . $inputID . '" ';
        }
               
        if( isset( $attrsRules['required'] ) && strpos( $attrsRules['required'], $attribute ) !== FALSE ) {
            $label .= 'class="requiredField';
            if( array_key_exists( 'class', $options ) ){
                $label .= ' ' . $options['class'] . '" ';
                unset( $options['class'] );
            }
            else{
                $label .= '" ';
            }
        }
        
        foreach ( $options as $attrib => $val ) {
            $label .= $attrib . '="' . $val . '" ';
        } 
        $label .= '>';
        
        $label .= $lbl;
        $label .= '</label>';
        return $label;
    }
    
    /**
     * Statická metóda ktorá vytvorí input[type=text]
     * @return string vytvorený HTML element
     * @param object $model
     * @param String $attribute
     * @param array $options [optional] 
     * @param boolean $bulk [optional] Jeden vstup alebo pole []
     * @return String vytvorený HTML element
     */
    public static function mTextInput( $model, $attribute, $options = array(), $bulk = FALSE ){
        
        return MhtmlCore::createInput('text', $model, $attribute, $options, $bulk);
        
    }
    
    /**
     * Statická metóda ktorá vytvorí input[type=password]
     * @return string vytvorený HTML element
     * @param object $model
     * @param String $attribute
     * @param array $options [optional] 
     * @param boolean $bulk [optional] Jeden vstup alebo pole []
     * @return String vytvorený HTML element
     */
    public static function mPasswordInput( $model, $attribute, $options = array(), $bulk = FALSE ){
        
        return MhtmlCore::createInput('password', $model, $attribute, $options, $bulk);
    }
    
    /**
     * Statická metóda ktorá vytvorí input[type=hidden]
     * @param object $model
     * @param String $attribute
     * @param array $options [optional] 
     * @param boolean $bulk [optional] Jeden vstup alebo pole []
     * @return String vytvorený HTML element
     */
    public static function mHiddenInput( $model, $attribute, $options = array(), $bulk = FALSE ){
        
        return MhtmlCore::createInput('hidden', $model, $attribute, $options, $bulk);
    }
    
    /**
     * Statická metóda ktorá vytvorí input[type=radio]
     * @param object $model
     * @param String $attribute
     * @param array $options [optional] 
     * @param boolean $bulk [optional] Jeden vstup alebo pole []
     * @return String vytvorený HTML element
     */
    public static function mRadioButton( $model, $attribute, $options = array(), $bulk = FALSE ){
        
        return MhtmlCore::createInput('radio', $model, $attribute, $options, $bulk);
    }
    
    /**
     * Statická metóda ktorá vytvorí input[type=checkbox]
     * @param object $model
     * @param String $attribute
     * @param array $options [optional]
     * @param boolean $bulk [optional] Jeden vstup alebo pole []
     * @return String vytvorený HTML element
     */
    public static function mCheckbox( $model, $attribute, $options = array(), $bulk = FALSE ){
        
        return MhtmlCore::createInput('checkbox', $model, $attribute, $options, $bulk);
    }
    
    /**
     * Metóda pre zjednodušenie vytvárania inputov. Vytvorí input na základe špecifikovaného typu
     * @param String $type
     * @param object $model
     * @param String $attribute
     * @param array $options [optional] 
     * @return String vytvorený HTML element
     * 
     * @todo Resolve current state: Bulk inputs with same ID - invalid HTML
     */
    protected static function createInput( $type, $model, $attribute, $options = array(), $bulk = FALSE ){
        
        $field = '<input type="' . $type . '" ';
        
        if( !array_key_exists( 'name', $options ) ) {
            $field .= 'name="' . get_class($model) . '[' . $attribute . ']' . ($bulk? '[]" ' : '" ') ;
        }
        elseif( $bulk ){
            $field .= 'name="' . $options['name'] . '[' . $attribute . '][]" ';
            unset($options['name']);
        }
        
        if( property_exists( $model, $attribute ) ) {
            $field .= 'value="' . $model->$attribute . '" ';
        }
        elseif( isset( $model->attributes ) && array_key_exists( $attribute, $model->attributes)){
            $field .= 'value="' . $model->attributes[$attribute] . '" ';
        }
        
        if( !array_key_exists( 'id', $options ) ) {
            $field.= 'id="' . get_class($model) . '_'.$attribute . '" ';
        }
        
        if( method_exists( $model, 'getTags') && ($placeholder = $model->getTags( $attribute )['placeholder']) != NULL){
            $field .= 'placeholder="' . $placeholder . '" ';
        }
        
        foreach ( $options as $attrib => $val ) {
            $field .= $attrib . '="' . $val . '" ';
        } 
        $field .= ">";
        
        return $field;
    }
    
    /**
     * Metóda vytvorí HTML element TEXTAREA
     * @param object $model
     * @param String $attribute
     * @param array $options [optional]
     * @param boolean $bulk [optional] Jeden vstup alebo pole []
     */
    public static function mTextarea( $model, $attribute, $options = array(), $bulk = FALSE ){
        
        $tArea = '<textarea ';

        if( !array_key_exists( 'name', $options ) ) {
            $tArea .= 'name="' . get_class($model) . '[' . $attribute . ']' . ($bulk? '[]" ' : '" ') ;
        }
        elseif( $bulk ){
            $tArea .= 'name="' . $options['name'] . '[' . $attribute . '][]" ';
            unset($options['name']);
        }
        
        if( !array_key_exists( 'id', $options ) ) {
            $tArea.= 'id="' . get_class($model) . '_'.$attribute . '" ';
        }
        
        foreach ( $options as $attrib => $val ) {
            $tArea .= $attrib . '="' . $val . '" ';
        } 
        $tArea .= ">";
        
        if( property_exists( $model, $attribute ) ) {
            $tArea .= $model->$attribute . '</textarea>';
        }
        elseif( isset( $model->attributes ) && array_key_exists( $attribute, $model->attributes)){
            $tArea .= $model->attributes[$attribute] . '</textarea>';
        }
        else{
            $tArea .= '</textarea>';
        }
        
        return $tArea;
    }
    
    /**
     * Metóda vytovorí vlastný input[type=number] s možnosťou navýšovania hodnoty tlačidlami
     * @param object $model
     * @param String $attribute
     * @param array $inputOptions [optional] HTML možnosti pre input  
     * @param array $wrapOptions [optional] HTML možnosti pre kontajner  
     */
    public static function minputRange( $model, $attribute, $inputOptions = array(), $wrapOptions = array() ) {
        
        $ret = '<div class="minputrange ';
        
        if( array_key_exists( 'class', $wrapOptions ) ) {
            $ret .= $wrapOptions['class'] . '"';
        }else{
            $ret .= '" ';
        }
        
        foreach ( $wrapOptions as $attrib => $val ) {
            $ret .= $attrib . '="' . $val . '" ';
        } 
        $ret .= ">";
        
            $ret .= MhtmlCore::createInput('text', $model, $attribute, $inputOptions);
            $ret .= '<div class="buttons">'; 
                $ret .= '<div class="up"></div>'; 
                $ret .= '<div class="down"></div>'; 
            $ret .= '</div>'; 
        $ret .= '</div>'; 
        return $ret;
    }
    
    /**
     * Metóda vytvorí customizovaný drop down list
     * @param String $buttonName Názov hlavného tlačidla
     * @param array $values Možnosti výberu
     * @param array $htmlOptions [optional] Indexované pole html atribúty pre každú možnosť zvlášť, 
     * formát atribút=>hodnota, napr. "style"=>"color: #fff"
     */
    public static function mdropdown( $buttonName, $values, $htmlOptions = array() ){
        
        $iter = 0;
        
        $ret = '<div class="mdropdown">';
            $ret .= '<a class="styled-button btn btn-default">' . $buttonName . '&nbsp;<span class="caret"></span></a>';
            $ret .= '<div class="droplist">';
            foreach ( $values as $value ){
                $ret .= '<a class="mclickable ';
                if( isset( $htmlOptions[$iter]["class"] ) ){
                    $ret .= $htmlOptions[$iter]["class"] . '" ';
                    unset( $htmlOptions[$iter]["class"] );
                }
                else{
                    $ret .= '"';
                }
                if( !empty($htmlOptions) ) {
                    foreach ( $htmlOptions[$iter] as $attr => $val ){
                        $ret .= $attr . '="' . $val . '" ';
                    }
                }
                $ret .= ">{$value}</a>";
                $iter++;
            }
        $ret .= '</div></div>';
        return $ret;
    }
    
    /**
     * Metóda vytovrí jednoduchý html select
     * 
     * @param object $model
     * @param String $attribute
     * @param array $values [optional] Pole hodnôt  
     * @param array $options [optional] HTML možnosti pre select  
     * @return String HTML Snippet
     */
    public static function mSelect( $model, $attribute, $values = array() ,$options = array(), $bulk = FALSE ){
        
        $select = '<select ';
        
        if( !array_key_exists( 'name', $options ) ) {
            $select .= 'name="' . get_class($model) . '[' . $attribute . ']' . ($bulk? '[]" ' : '" ') ;
        }
        elseif( $bulk ){
            $tArea .= 'name="' . $options['name'] . '[' . $attribute . '][]" ';
            unset($options['name']);
        }
        
        if( !array_key_exists( 'id', $options ) ) {
            $select.= 'id="' . get_class( $model ) . '_'.$attribute . '" ';
        }
        
        foreach ( $options as $attrib => $val ) {
            $select .= $attrib . '="' . $val . '" ';
        } 
        $select .= ">";
        
        foreach ( $values as $value => $name ){           
            $select .= '<option value="' . $value .'" '; 
            if( property_exists( $model, $attribute ) && $model->$attribute == $value ) {
                $select .= ' selected="selected"';
            }
            elseif( isset( $model->attributes ) && array_key_exists( $attribute, $model->attributes )
                    && $model->attributes[$attribute] == $value ){
                
                $select .= ' selected="selected"';
            }
            
            $select .= '>' . $name.'</option>'; 
        }
        $select .= '</select>';
        
        return $select;
    }
    
    /**
     * Metóda vytvorí breadrumbs - Omrvinkovú navigáciu
     * @param array $levels Úrovne navigácie
     * @param boolean $urlChaining Levelové spájanie url adries /kategoria/podkategoria
     * @param boolean $useSubdomain Prva hodnota z pola $levels je vlozena ako subdomena
     * @param boolean $lastPlain Posledny 
     */
    public static function createBreadcrumbs( $levels, $urlChaining = TRUE, $useSubdomain = FALSE, $lastPlain = FALSE ) {
        
        $breadcrumbs ='<ol class="breadcrumb">';
        
            $breadcrumbs .= '<li itemscope itemtype="http://data-vocabulary.org/Breadcrumb">';
            $breadcrumbs .= '<a href="' . ROOT_URL . '" itemprop="url">';
            $breadcrumbs .= '<span itemprop="title">' . MAGNECOMF_ESHOPNAME . '</span>';
            $breadcrumbs .= '</a></li>';
            
            $subdomain = $last = $url = '';
            $entry_url = ENTRY_SCRIPT_URL;
            
            if( $lastPlain && !empty($levels) ){
                $last = array_pop($levels);
            }
            
            //** First item with subdomain link
            if( $useSubdomain && !empty($levels)){
                $subdomain    = array_shift( $levels );
                $entry_url    = self::createSubdomainLink($subdomain->url);
                
                $breadcrumbs .= '<li itemscope itemtype="http://data-vocabulary.org/Breadcrumb">';
                $breadcrumbs .= '<a href="' . $entry_url . '" title="' . $subdomain->name . '" itemprop="url">';
                $breadcrumbs .= '<span itemprop="title">' . $subdomain->name . '</span>';
                $breadcrumbs .= '</a></li>';
            }
            
            //** Create breadcrumb levels
            foreach ($levels as $level){
                if( empty( $level ) ){
                    continue;
                }
                if( substr( $level->url, -1, 1 ) == '/') {
                    $level->url = substr( $level->url, 0, -1 );
                }
                if( $urlChaining ){
                    $url .= $level->url . '/';
                }
                else {
                    $url = $level->url . '/';
                }            
                
                $breadcrumbs .= '<li itemscope itemtype="http://data-vocabulary.org/Breadcrumb">';
                $breadcrumbs .= '<a href="' . $entry_url . substr( $url, 0, -1 ) . '" title="' . $level->name . '" itemprop="url">';
                $breadcrumbs .= '<span itemprop="title">' . $level->name . '</span>';
                $breadcrumbs .= '</a></li>';
            }
            
            //** Append last item
            if( !empty($last) ){
                $breadcrumbs .= '<li itemscope itemtype="http://data-vocabulary.org/Breadcrumb">';
                $breadcrumbs .= '<span itemprop="title">' . $last->name . '</span>';
                $breadcrumbs .= '</li>';
            }
             
        return $breadcrumbs . '</ol>';
    }
    
    /**
     * Vytvorí lištu so stránkovaním
     * @param array $pagination Pole s povinnými atribútami 'current' a 'total'
     * @param int $step Krok - Nasobí číslo strany
     * @param String $href_start - Začiatok odkazu za, ktorý sa priradí (strana * $step)
     * @return String 
     */
    public static function createPagination( $pagination, $step = 1, $href_start = '?page=' ){
        
        $pg = '<ul class="pagination">';
         
            //** Left arrow block
            if( $pagination['current'] != 1 ){
                $pg .= '<li><a href="' . $href_start.( ( $pagination['current'] - 2 ) * $step );
                $pg .= 'title="' . Mcore::t('Previous page') . '" rel="nofollow">'; //spatne odkazy nenasleduje
                $pg .= '&laquo;</a></li>';
            }
            else{
                $pg .= '<li class="disabled"><a href="#">&laquo;</a></li>';
            }

            //** Number block
            $start = 0;
            if( $pagination['current'] - 3 > 1){
                $pg .= '<li><a href="' . $href_start . '0" rel="nofollow">1</a></li>';
                $pg .= '<li class="disabled"><a href="#">...</a></li>';
                $start = $pagination['current'] - 3;
            }

            $end = ( $pagination['current'] + 2 > $pagination['total'] ) ? $pagination['total']-1 : $pagination['current'] + 1;
            for( $i = $start; $i <= $end ; $i++){
                $pg .= '<li><a href="' . $href_start . $i * $step . '" rel="nofollow">' . ($i + 1) . '</a></li>';
            }
            if( $pagination['current'] + 3 < $pagination['total'] ){
                $pg .= '<li class="disabled"><a href="#">...</a></li>';
            }
            if( $pagination['current'] + 2 < $pagination['total'] ){                        
                $pg .= '<li><a href="' . $href_start . ( $pagination['total'] - 1 ) * $step . '" rel="nofollow">' . $pagination['total'] . '</a></li>';
            }

            //** Right arrow block
            if ( $pagination['current'] != $pagination['total']) {
                $pg .= '<li><a href="' . $href_start . ( ($pagination['current'] ) * $step ) . '" ';
                $pg .= 'title="' . Mcore::t('Next page') . '" rel="nofollow">'; 
                $pg .= '&raquo;</a></li>';
            }
            else{
                $pg .= '<li class="disabled"><a href="#">&raquo;</a></li>';
            }
        $pg .= '</ul>';
        
        return $pg;
    }


    /**
     * Metoda modifikuje URL adresu, pridá retazec ako subdoménu 
     * alebo ho pripojí na koniec URL adresy, uvedej ako parameter $url
     * 
     * @param String $subdomain Retazec, ktorý sa v prípade, že je definovaná 
     *        konštanta MCORE_SUBDOMAIN_USE vlozi do parametra $url ako subdoména, 
     *        v opacnom prípade sa pripojí na koniec etazca $url a ukončí sa '/'
     * @param String $url URL adresa, ktora sa spracuje
     * @return String URL adresa ukoncená znakom '/'
     */
    public static function createSubdomainLink( $subdomain, $url = ENTRY_SCRIPT_URL ){
        
        $_s = Mcore::base()->getSetting( 'MCORE_SUBDOMAIN_USE' );
        
        if( isset( $_s ) && $_s === TRUE ){
            return preg_replace( '/:\/\/(www\.)?/', "://{$subdomain}.", $url );
        }
        elseif( !empty ($subdomain) ){
            return $url . $subdomain . '/';
        }
        else{
            return $url;
        }
    }

    /**
     * Metóda parsuje, zadaný vstup a vytvorí validnú URL adresu
     * @param String $in
     * @return String 
     */
    public static function createUrl( $in ){
        
        $tr_pairs = array("ľ" => "l", "š" => "s", "č" => "c",
                            "ť" => "t", "ž" => "z", "ý" => "y", "á" => "a", "í" => "i", 
                            "é" => "e", "ú" => "u", "ä" => "a", "ô" => "o", "ó" => "o", 
                            "ď" => "d", "ľ" => "l", "ň" => "n", "ö" => "o", "ü" => "u");
        
        $out = mb_strtolower( $in, 'UTF-8' );
        $out = strtr( $out, $tr_pairs );// odstrániť diakritiku
        $out = preg_replace( '/[^a-z0-9]/', '-', $out );// ostatné znaky nahradí pomlčkou

        // nechá maximálne 1 pomlčku, odstrani pomlcku zozaciatku a z konca
        $out = preg_replace( array('/\-+/', '/\-+$/', '/^\-+/' ), array('-','',''), $out ); 

        return ( substr( $out, 0, 100 ) );
    }
    
    /**
     * Pomocna metoda ktora vrati cast stringu, odstranenie bugu kodovania pri substr a nefunkcnosti mb_substr
     * @param String $str
     * @param int $s start position
     * @param int $l length
     */
    public static function substr_unicode($str, $s, $l = null) {
        
        return join("", array_slice(
            preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY), $s, $l));
    }
    
    /**
     * Metoda generuje nahodny retazec znakov.
     *
     * @param integer $amount Dĺžka reťazca ktorý má byť vygenerovaný
     * @return string Vygenerovaný nahodný string pozadovanej dĺžky
     */
    public static function randString($amount){ 

        $characters = 'abcdefghijklmnopqrstuvx0123456789'; 
        $length_chracters = strlen($characters); 
        $length_chracters--; 

        $hash = NULL; 
        for( $i = 1; $i <= $amount; $i++ ){ 
            $temp  = rand( 0, $length_chracters ); 
            $hash .= substr( $characters, $temp, 1 ); 
        } 

        return $hash; 
    } 
}