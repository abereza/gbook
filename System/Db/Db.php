<?php
class Db {
    private static $m_pObj = NULL;
    
    private $m_dbConn;
    
    private function __construct() {
    }
    
    private function __clone() {
    }
        
    private function __wakeup() {
    }
   
    public static function getInstance() {
        if (is_null(self::$m_pObj)) {
            self::$m_pObj = new self;
        }
        
        return self::$m_pObj;
    }
    
    public function create($aConfig) {
        /// \@todo: нужна ли тут проверка на валидность aConfig  и как правильно она делается?
        if (is_null($aConfig)) {
            return false;
        }
        
        if (!($this->m_dbConn = pg_connect("host=" . $aConfig["host"] 
                . " dbname=" . $aConfig["dbname"]
                . " user=" . $aConfig["login"]
                . " password=". $aConfig["passwd"]))) {
            throw new Exception('Failed in '.__CLASS__.'::'.__METHOD__.' at line '.__LINE__);
        }
        
        return true;
    }
            
    public function query($sSqlString, $aParams = null) {
        if (is_null($aParams)) {
            $result = pg_query($this->m_dbConn, $sSqlString);
        }
        else {
            if (is_null(pg_prepare($this->m_dbConn, "", $sSqlString))) {
                throw new Exception('Failed in '.__CLASS__.'::'.__METHOD__.' at line '.__LINE__);
            }
                $result = pg_execute($this->m_dbConn, "", $aParams);
        }
        
        return new Selector($result);
    }
    
}
