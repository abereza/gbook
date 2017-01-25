<?php
abstract class Model {
    const MODEL_USER = "Model_User";
    const MODEL_MESG = "Model_Message";
    
    protected $m_sTableName;
    
    protected $m_sPkField;
    
    protected $m_sPkValue;
    
    protected $m_aParam;
    
    protected $m_bLoadFlag = null;
    
    protected $m_aWhere;
    
    protected $m_aOrder;
    
    protected $m_aLimit;


    protected function prepareParamKeys()
    {
        $sQuery = "SELECT * FROM " . $this->m_sTableName . ";";
        
        $oResult = Db::getInstance()->query($sQuery);
        
        $aResult = $oResult->fetchRow(0);
                
        $this->m_aParam = array_fill_keys(array_keys($aResult), null); 
 
    }
    
    
    protected function query() {
        $sQuery = "SELECT * FROM " . $this->m_sTableName;
        $aVars = NULL;
        
        if (!empty($this->m_aWhere)) {
            $aVars = array_values($this->m_aWhere);
            $sQuery .= $this->addWhereString();
        }
       
        if (!empty($this->m_aOrder)) {
            $sQuery .= $this->addOrderString();
        }
        
        if (!empty($this->m_aLimit)) {
            $sQuery .= $this->addLimitString();
        }
  
        return Db::getInstance()
                ->query($sQuery.";", $aVars)
                ->fetchAll();
    }
    
    protected function addWhereString() {
        $sTail = " WHERE ";
         
        $idx = 1;
            
        foreach (array_keys($this->m_aWhere) as $field) {
            $sTail .= $field . "=$" . $idx++;
            if ($idx <= count($this->m_aWhere)) {
                $sTail .= " AND ";
            }
        }
        $this->m_aWhere = array();
        
        return $sTail;
    }
    
    
    protected function addOrderString() {
        /// \@fixme: пока только один элемент
        $sTail = " ORDER BY " . key($this->m_aOrder) . 
                 " " . current($this->m_aOrder);
        
        $this->m_aOrder = array();
        
        return $sTail;
    }
    

    protected function addLimitString() {
        $sTail = " LIMIT " . key($this->m_aLimit) . 
                 " OFFSET " . current($this->m_aLimit);
        
        $this->m_aLimit = array();
        
        return $sTail;
    }
    

    protected function load($iId) {
        $sQuery = "SELECT * FROM " . $this->m_sTableName . 
                  " WHERE " . $this->m_sPkField . "=$1;";
        
        $oResult = Db::getInstance()->query($sQuery, array($iId));

        if ($oResult->isEmpty()) {
            return FALSE;
        }
        
        $aResult = $oResult->fetchRow(0);
                
        $this->m_aParam = array_combine(array_keys($aResult), 
                                        array_values($aResult));        
   
        $this->m_bLoadFlag = true;
        
        return true;
    }


    public static function factory($sModelName, $iId = null) {
        switch ($sModelName) {
            case self::MODEL_USER:
                return new Model_User($iId);
            case self::MODEL_MESG:
                return new Model_Message($iId);
        }
        throw new Exception('Failed in '.__CLASS__.'::'.__METHOD__.' at line '.__LINE__);
    }
       
    
    public function __construct($iId = null) {
        if (!is_null($iId)) {
            if ($this->load($iId)) {
                $this->m_sPkValue = $iId;
            }
            else {
                throw new Exception('Failed in '.__CLASS__.'::'.__METHOD__.' at line '.__LINE__);
            }
        }
        else {
            $this->prepareParamKeys();
        }
    }
    
    public function __get($sVarName) {
        if (array_key_exists($sVarName, $this->m_aParam)) {
            return $this->m_aParam[$sVarName];
        }
        throw new Exception('Failed in '.__CLASS__.'::'.__METHOD__.' at line '.__LINE__);
    }
    
    
     public function where($aParam) {
        if (!empty($aParam)) {
            if (empty($this->m_aWhere)) {
                $this->m_aWhere = $aParam;
            } 
            else {
                $this->m_aWhere = array_merge($this->m_aWhere, $aParam);
            }
        }
        
        return $this;
    }
    
    public function order($aParam) {
        if (!empty($aParam)) {
            $this->m_aOrder = $aParam;
        }
        
        return $this;
    }
    
    
    public function limit($aParam) {
        if (!empty($aParam)) {
            $this->m_aLimit = $aParam;
        }
        
        return $this;
    }

    
    public function fetchAll() {
        $aObjects = array();
        
        $aResult = $this->query(); 
        
        if (!empty($aResult)) {
            foreach ($aResult as $row) {
                /// \@wtf : а ведь можно передать сразу id  и уменьшить число строк,
                ///         но это дополнительное обращение  к БД..не надо ж?
                $obj = Model::factory(get_class($this));
                $obj->values($row);
                array_push($aObjects, $obj);
            }
        }
        
        return $aObjects;
    }
    
    
    public function rowCount() {
        $sQuery = "SELECT COUNT(*) FROM " . $this->m_sTableName . ";"; 
        
        $oResult = Db::getInstance()->query($sQuery);
        
        return $oResult->fetchOne(0, 0);
    }
    
    public function save($bFlagUp = null) {
        $idx = 0;
        $sParamsKey = $sParamsValTmpl = ""; 
        $aParamsVal = array();
        
        foreach ($this->m_aParam as $key => $value) {
            if (!$idx) {
                $idx++;
                continue; //поле id пропустим
            }
            
            $aParamsVal[$idx++] = $value;
            $sParamsKey .= $key;
            $sParamsValTmpl .= "$".($idx - 1);
            
            if ($idx < count($this->m_aParam)) {
                $sParamsKey .= ", ";
                $sParamsValTmpl .= ", ";
            }
        }        
        
        if ($bFlagUp) {
            $sQuery = "UPDATE ".$this->m_sTableName." SET (".$sParamsKey
                      .") = (".$sParamsValTmpl
                      .") WHERE ".$this->m_sPkField."=".$this->m_aParam[$this->m_sPkField]
                      .";";
        } else {
            $sQuery = "INSERT INTO ".$this->m_sTableName." (".$sParamsKey
                      .") VALUES (". $sParamsValTmpl
                      .");";
        }

        Db::getInstance()->query($sQuery, $aParamsVal);
        
        $this->m_bLoadFlag = true;
    }
        

    public function loaded(){
        return $this->m_bLoadFlag;
    }
       
    
    public function values($aParam) {
        foreach ($aParam as $key => $value) {
            $this->m_aParam[$key] = $value;
        }
    }   
}