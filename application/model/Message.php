<?php
class Model_Message extends Model {
    public function __construct($iId = null) {
        $this->m_sTableName = "\"RECORDS\"";
        
        $this->m_sPkField = "id";
        
        parent::__construct($iId);
    }
}