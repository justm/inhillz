<?php

/**
 * Súbor obsahuje triedu MmodelCore
 *
 * @author Matus Macak < matus.macak@folcon.sk > 
 * @link http://www.folcon.sk/
 * @version 1.1 Mercury drop
 * @since Subor je súčasťou aplikácie od verzie 1.0
 * @package mcore.mabstract
 * 
 */

/**
 * Trieda MmodelCore predtavuje základnú funckionalitu, ktorú by mal 
 * zdediť každý model v tomto frameworku
 */
abstract class MmodelCore {

    /**
     * @var array Pole attribútov (stĺpcov tabuľky)
     */
    public $attributes = array();

    /**
     * @var array Pole attribútov (stĺpcov tabuľky), počas behu scriptu nedôjde k ich zmene
     * využiju sa na kontrolu pri ukladaní objektu do DB
     */
    protected $originalAttributes = array();

    /**
     * @var String Názov databázovej tabuľky
     */
    protected $tableName;

    /**
     * @var array pole stĺpcov ktoré majú v DB nejakú default hodnotu
     */
    protected $columnsWithDefValue = array();

    /**
     * @var String Názov stĺpca s primárnym kľúčom
     */
    public $primaryKey;

    /**
     * @var boolean Definuje či sa jedná o nový záznam = nezápísaný v DB alebo existujúci
     */
    protected $isNewRecord = TRUE;

    /**
     * @var String reťazec pre formulár s upozorneniami pri povinných položkách
     * alebo nesprávnom formáte
     */
    protected $validationErrors = NULL;

    /**
     * @var array Uchováva modely ktoré zdedili funckionalitu
     */
    private static $models = array();

    /**
     * Konštruktor modelu, zistí atribúty databázovej tabuľky a priradí ich do poľa attributes
     */
    public function __construct() {

        $this->tableName = $this->table();
        $registry = Mcore::base();
        $registry->getObject('db')->executeQuery('SHOW columns FROM `' . strtolower($this->table()) . '`');
        $result = $registry->getObject('db')->getObjectRows();
        foreach ($result as $row) {
            if ($row->Key == 'PRI') {
                $this->primaryKey = $row->Field;
            }
            if ($row->Default != NULL) {
                $this->columnsWithDefValue[] = $row->Field;
            }

            $this->originalAttributes[$row->Field] = $this->attributes[$row->Field] = NULL;
        }
    }

    /**
     * Metóda vráti model požadovanej triedy (potomka)
     * @return object 
     */
    public static function model($className = __CLASS__) {

        if (isset(self::$models[$className])) {
            return self::$models[$className];
        } else {
            $model = self::$models[$className] = new $className(null);
            return $model;
        }
    }

    /**
     * PHP getter magic method
     * Umožňuje pristupovať sĺpcom databázy ako k atribútom objektu, hoci v ňom nie sú definované
     * @param String $name Názov virtuálneho atribútu
     */
    public function __get($name) {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        } else {
            trigger_error('Objekt ' . get_class($this) . ' neobsahuje atribút ' . $name, E_USER_NOTICE);
        }
    }

    /**
     * PHP setter magic method
     * Umožňuje zmeniť hodnotu sĺpcom databázy ako atribútom objektu, hoci v ňom nie sú definované
     * @param String $name Názov virtuálneho atribútu
     * @param mixed $value Hodnota, ktorá bude priradená
     */
    public function __set($name, $value) {

        if ($this->setAttribute($name, $value) === false) {
            trigger_error('Objekt ' . get_class($this) . ' neobsahuje atribút ' . $name, E_USER_NOTICE);
        }
    }

    /**
     * PHP isset magic method
     * Metóda zistí či je virtuálny atribút incializovaný alebo nie
     * @param String $name Názov virtuálneho atribútu
     * @return boolean
     */
    public function __isset($name) {

        if (isset($this->attributes[$name])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * PHP unset magic method
     * Umožňuje nastaviť hodnotu virtuálnemu atribútu na NULL
     * @param String $name Názov virtuálneho atribútu
     */
    public function __unset($name) {

        if (array_key_exists($name, $this->attributes)) {
            unset($this->attributes[$name]);
        }
    }

    /**
     * Metóda vráti hodnotu premennej $isNewRecord, ktora definuje či sa jedna o novy zaznam
     * @return boolean
     */
    public function getIsNewRecord() {
        return $this->isNewRecord;
    }

    /**
     * Metóda nastaví hodnotu premennej $isNewRecord, ktora definuje či sa jedna o novy zaznam
     */
    public function setIsNewRecord($isNewRecord) {

        $this->isNewRecord = $isNewRecord;
    }

    /**
     * Metóda vráti pole atribútov
     * @return array
     */
    public function getAttributes() {
        return $this->attributes;
    }

    /**
     * Metóda získa hodnotu požadovaného atribútu
     * @param String $name Názov atribútu
     * @return mixed Hodnota atribútu, NULL ak nie je definovaná alebo neexistuje
     */
    public function getAttribute($name) {
        if (property_exists($this, $name)) {
            return $this->$name;
        } elseif (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        } else {
            return NULL;
        }
    }

    /**
     * Metóda nastaví hodnotu špecifikovaného atribútu na požadovanú hodnotu
     * @param String $name Názov atribútu
     * @param mixed $value hodnota atribútu
     * @return boolean Definuje či bola operácia prevedená
     */
    public function setAttribute($name, $value) {
        if (array_key_exists($name, $this->attributes)) {
            $this->attributes[$name] = $value;
            return true;
        } else {
            return false;
        }
    }

    /**
     * Metóda nastaví atribúty poľa na hodnoty definované parametrom
     * @param array $vals Asociatívne pole stĺpec => hodnota
     * @return array
     */
    public function setAttributes($vals) {

        foreach ($vals as $key => $val) {
            $this->attributes[$key] = $val;
        }
    }
         
    /**
     * Vráti upozornenia na validačné chyby
     * @return array
     */
    public function getValidationErrors() {
        return $this->validationErrors;
    }

    /**
     * Nastaví upozornenie na validačné chyby
     * @param array $validationErrors
     */
    public function setValidationErrors($validationErrors) {
        $this->validationErrors = $validationErrors;
    }
    
    /**
     * Skontroluje či model má validačné chyby
     * @return bool TRUE ak model má chyby, false ak nemá
     */
    public function hasValidationErrors() {
        return !empty($this->validationErrors);
    }   
        
    public function getPrimaryKey() {
        return $this->primaryKey;
    }

    /**
     * Metóda vyhľadá jeden záznam spĺňajúci zadanú podmienku
     * 
     * V SQL príkaze je tabuľka pre aktuálny model označená aliasom t
     * @param String condition
     * @param String $columns [optional]
     * @param String $joins [optional] Definícia JOIN
     * @return object | NULL
     */
    public function find($condition, $columns = '*', $joins = '') {

        $sql = "SELECT {$columns} FROM `{$this->table()}` t {$joins} WHERE {$condition} LIMIT 1";
        $registry = Mcore::base();
        $registry->getObject('db')->executeQuery($sql);

        //echo $sql . '<br />';
        //trigger_error($sql, E_USER_NOTICE); 

        if ($registry->getObject('db')->getNumRows() != 0) {
            $res = $registry->getObject('db')->getArrayRows();
            $model = new $this;
            $model->setIsNewRecord(FALSE);
            $model->setAttributes($res[0]);
            $model->afterFind();
            return $model;
        } else {
            return NULL;
        }
    }

    /**
     * Metóda vyhľadá zaznam podľa zadaného ID
     * 
     * V SQL príkaze je tabuľka pre aktuálny model označená aliasom t
     * @param mixed $id
     * @param String $columns [optional]
     * @param String $joins [optional] Definícia JOIN_test
     * @return object Objekt tejto triedy
     */
    public function findById($id, $columns = '*', $joins = '') {

        if ( $id == number_format(intval($id),0) ) {
            $condition = "t.`id`= {$id}";
        } elseif (!isset($id)) {
            $condition = "t.`id`= 0";
        } else {
            $condition = $id;
        }
        $sql = "SELECT {$columns} FROM `{$this->table()}` t {$joins} WHERE {$condition} LIMIT 1";
        $registry = Mcore::base();
        //echo $sql;

        $registry->getObject('db')->executeQuery($sql);

        if ($registry->getObject('db')->getNumRows() != 0) {
            $res = $registry->getObject('db')->getArrayRows();
            $model = new $this;
            $model->setIsNewRecord(FALSE);
            $model->setAttributes($res[0]);
            $model->afterFind();
            return $model;
        } else {
            return NULL;
        }
    }

    /**
     * Metóda vyhľadá všetky záznamy spĺňajúce danú podmienku
     * 
     * V SQL príkaze je tabuľka pre aktuálny model označená aliasom t
     * @param String $condition [optional]
     * @param String $columns [optional]
     * @param String $joins [optional] Definícia JOIN
     * @param String $index [optional] Názov stĺpca ktorého hodnota bude predstavovať kľúč,
     *  vo výslednom poli. Odporúča sa používať stĺpce s unikátmi hodnotami 
     * @return array Pole objektov tejto triedy
     */
    public function findAll($condition = '', $columns = '*', $joins = '', $index = '') {

        $sql = "SELECT SQL_CACHE {$columns} FROM `{$this->table()}` t {$joins} ";
        if ($condition != '') {
            $sql .= "WHERE {$condition}";
        }
        //echo $sql . '<br /><br />'; 
        $emptyObject = new $this;
        $emptyObject->setIsNewRecord(FALSE);

        $DB = Mcore::base()->getObject('db');
        $DB->executeQuery($sql);

        if ($DB->getNumRows() != 0) {

            $res = $DB->getObjectRows();
            $arrayRes = array();
            foreach ($res as $row) {
                $model = clone $emptyObject;
                $model->setAttributes($row);
                if ($index != '' && ( ( $key = $model->getAttribute($index) ) != NULL )) {
                    $arrayRes[$key] = $model;
                } else {
                    $arrayRes[] = $model;
                }
            }
            return $arrayRes;
        } else {
            return NULL;
        }
    }
    
    /**
     * Preťaženie funkcie umožňuje vykonať úpravy v modeli po vyhľadaní záznamu cez metódy find(), findById()
     * @since 2.0
     */
    protected function afterFind(){ }

    /**
     * Metóda validuje dáta pred uložením do DB
     * @version 1.1 Do pravidiel pridany kluc 'allowHTML', z atributov ktore nepouziju tento kluc su odstranene html tagy
     * @return boolean - v závislosti od úspešnosti validácie
     */
    public function validate() {

        $rules = $this->rules();
        $labels = $this->labels();

        foreach ($this->attributes as $attrib => $value) {
            //Povinne polozky
            if (isset($rules['required']) && preg_match('/\b' . $attrib . '\b/', $rules['required']) != 0) {

                if ($this->primaryKey !== $attrib && !in_array($attrib, $this->columnsWithDefValue) && (!isset($value) || empty($value) )) {

                    $this->validationErrors .=
                            '<li><strong>' . (isset($labels[$attrib]) ? $labels[$attrib] : $attrib)
                            . '</strong> ' . Mcore::t('is required field') . '</li>';
                }
            }
            // Dlzka retazca
            if (isset($rules['length'])) {
                foreach ($rules['length'] as $length => $attribList) {
                    if (preg_match('/\b' . $attrib . '\b/', $attribList) != 0 && strlen($value) > $length) {
                        $this->validationErrors .=
                                '<li><strong>' . (isset($labels[$attrib]) ? $labels[$attrib] : $attrib)
                                . "</strong> má povolenú dĺžku <strong>{$length}</strong> znakov</li>";
                    }
                }
            }

            if (isset($rules['unique']) && strpos($rules['unique'], $attrib) !== false) {

                if ($this->isNewRecord) {
                    $query = "SELECT t.{$attrib} as toCheck from {$this->table()} t WHERE t.{$attrib} = '{$value}';";
                } else {
                    $query = "SELECT t.{$attrib} as toCheck from {$this->table()} t WHERE t.{$attrib} = '{$value}' AND t.id != {$this->id};";
                }

                Mcore::base()->getObject('db')->executeQuery($query);
                $result = Mcore::base()->getObject('db')->getArrayRows();

                if (!empty($result)) {
                    $this->validationErrors .=
                            '<li>' . Mcore::t('Value in the field') . ' <strong>' . ( isset($labels[$attrib]) ? $labels[$attrib] : $attrib)
                            . '</strong> ' . Mcore::t('is already in use') . '</li>';
                }
            }
            if (isset($rules['format'])) {
                if (isset($rules['format']['email']) && preg_match('/\b' . $attrib . '\b/', $rules['format']['email']) != 0 && !empty($value)) {
                    if (@preg_match('{^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$}', $value) === 0) {
                        $this->validationErrors .=
                                '<li><strong>' . (isset($labels[$attrib]) ? $labels[$attrib] : $attrib)
                                . "</strong> nemá správny formát emailovej adresy</li>";
                    }
                }
                if (isset($rules['format']['numeric']) && preg_match('/\b' . $attrib . '\b/', $rules['format']['numeric']) != 0 && !empty($value) && !is_numeric($value)) {
                    $this->validationErrors .=
                            '<li><strong>' . (isset($labels[$attrib]) ? $labels[$attrib] : $attrib)
                            . "</strong> nemá platný numerický formát</li>";
                }
            }
            if (isset($rules['upperBound'])) {
                foreach ($rules['upperBound'] as $upperBound => $attribList) {
                    if (preg_match('/\b' . $attrib . '\b/', $attribList) != 0 && $value > $upperBound) {
                        $this->validationErrors .=
                                '<li><strong>' . (isset($labels[$attrib]) ? $labels[$attrib] : $attrib)
                                . "</strong> má maximálnu veľkosť <strong>{$upperBound}</strong></li>";
                    }
                }
            }

            if (isset($rules['lowerBound'])) {
                foreach ($rules['lowerBound'] as $lowerBound => $attribList) {
                    if (preg_match('/\b' . $attrib . '\b/', $attribList) != 0 && $value < $lowerBound) {
                        $this->validationErrors .=
                                '<li><strong>' . (isset($labels[$attrib]) ? $labels[$attrib] : $attrib)
                                . "</strong> má minimálnu veľkosť <strong>{$lowerBound}</strong></li>";
                    }
                }
            }

            //odstrani HTML znacky
            if (isset($rules['allowHTML'])) {
                if (preg_match('/\b' . $attrib . '\b/', $rules['allowHTML']) == 0 && $value != NULL) {
                    $this->attributes[$attrib] = strip_tags($value);
                }
            }
        }
        return !isset($this->validationErrors);
    }

    /**
     * Odstráni z atribútu formátovanie css
     * @param String $attribute Kľúč v poli attributes
     */
    public function stripcss_style($attribute) {

        if (isset($this->attributes[$attribute])) {
            $cssAttrs = array('font-family', 'line-height', 'font-size', 'font');

            $text = $this->attributes[$attribute];
            $matches = array();
            foreach ($cssAttrs as $cssAttr) {
                preg_match('/' . $cssAttr . '\s?:\s?[^;]+"/', $text, $match);
                $matches = array_merge($matches, $match);
            }
            (!empty($matches)) ? $text = str_replace($matches, '"', $text) : FALSE;

            $matches = array(); //Pripad ak je css hodnota ako posledna pred uvodzovkou
            foreach ($cssAttrs as $cssAttr) {
                preg_match('/' . $cssAttr . '\s?:\s?[^;]+;/', $text, $match);
                $matches = array_merge($matches, $match);
            }
            (!empty($matches)) ? $text = str_replace($matches, '', $text) : FALSE;

            $text = preg_replace('/style="[\s]+"/', '', $text); //odstrani prazdne style attributy
            $this->attributes[$attribute] = $text;
        }
    }

    /**
     * Metóda uloží záznam do databázy
     * @return boolean Oznámenie o úspešnom prevedení operácie
     */
    public function save($runValidation = false) {

        if (!$runValidation || $this->validate()) {
            return $this->getIsNewRecord() ? $this->insert() : $this->update();
        } else {
            return false;
        }
    }

    /**
     * Metóda vloží do DB nový záznam zodpovedajúci objektu tejto triedy
     * return boolean Oznámenie o uspešnosti prevedenia operácie
     * @return boolean Oznámenie o uspešnosti prevedenia operácie
     * @version 1.1 Odfiltruje vsekty atributy, ktore nepatria objektu,
     * nedojde k pokusu o ulozenie kedy bola casto vyhodena vynimka
     */
    public function insert() {

        $attributes = $this->attributes;
        foreach ($attributes as $key => $value) { //zahodi hodnoty neprisluchajuce stlpcom tabulky
            if (!array_key_exists($key, $this->originalAttributes)) {
                unset($attributes[$key]);
            }
        }
        if (array_key_exists($this->primaryKey, $this->attributes)) {
            unset($attributes[$this->primaryKey]);
        }

        $check = Mcore::base()->getObject('db')->insertRecords(
                $this->tableName, $attributes, $this->columnsWithDefValue);

        $this->attributes[$this->primaryKey] = Mcore::base()->getObject('db')->getLastInsertID();
        $this->isNewRecord = FALSE;

        return $check;
    }

    /**
     * Metóda upraví existujúci záznam v DB, ktorý zodpovedá objektu tejto triedy
     * @var String $whereClause Podmienka pre insert príkaz. Formát stĺpec = hodnota
     * @return boolean Oznámenie o uspešnosti prevedenia operácie
     * 
     */
    public function update($whereClause = '') {

        $attributes = $this->attributes;
        foreach ($attributes as $key => $value) { //zahodi hodnoty neprisluchajuce stlpcom tabulky
            if (!array_key_exists($key, $this->originalAttributes)) {
                unset($attributes[$key]);
            }
        }
        if (array_key_exists($this->primaryKey, $this->attributes)) {
            $pKey = $this->primaryKey;
            unset($attributes[$this->primaryKey]);

            if (is_numeric($this->attributes[$this->primaryKey]) &&
                    ( intval($this->attributes[$this->primaryKey]) == $this->attributes[$this->primaryKey] )) {
                $pKeyValue = $this->attributes[$this->primaryKey];
            } else {
                $pKeyValue = "'{$this->attributes[$this->primaryKey]}'";
            }
            $whereClause = "`{$pKey}` = {$pKeyValue}";
        } elseif ($whereClause == '') {
            throw new Exception("Tabuľka {$this->tableName} nemá primárny kľúč"
            . "a nebola špecifikovaná podmienka pre UPDATE príkaz.");
        }

        return Mcore::base()->getObject('db')->updateRecords(
                        $this->tableName, $attributes, $whereClause, $this->columnsWithDefValue);
    }

    /**
     * Metóda Vymaže existujúci záznam z DB, ktorý zodpovedá objektu tejto triedy
     * @return void
     */
    public function delete ( ) {
        
        return Mcore::base()->getObject('db')->deleteRecords( $this->tableName, "{$this->primaryKey} = '{$this->attributes[$this->primaryKey]}'" );
    }
    
    /**
     * Metóda, ktorá vráti pole definovaných označení pre jednotlivé atribúty modelu
     * @return array
     */
    public function labels(){
        
        return array();
    }
    
    /**
     * Used for a definition of 2 dimensional array of Label, note and Placeholder for each attribute
     * @since 2.0
     * @return array
     */
    protected function tags(){
        
        return array();
    }

    /**
     * Returns 2 dimensional array of Label, note and Placeholder for each attribute neccessary
     * @since 2.0
     * @param String $attribute Model Attribute name
     * @return Particular array for defined attribute or whole array
     */
    public function getTags( $attribute = NULL ){
        
        if( empty( $this->tags ) ){
            $this->tags();
        }
        if( !empty( $attribute ) && isset( $this->tags[$attribute] ) ){
            return $this->tags[$attribute];
        }
        else{
            return $this->tags;
        }
    }
    
    /**
     * Metóda vráti názov tabuľky v DB, ku ktorej prislúcha tento model
     * @return String Názov tabuľky
     */
    abstract public function table();
    
    /**
     * Metóda, ktorá vráti pole definovaných pravidiel pre jednotlivé atribúty modelu
     * @return array
     */
    abstract public function rules();
}
