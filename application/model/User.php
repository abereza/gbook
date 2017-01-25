<?php
class Model_User extends Model {
    public function __construct($iId = null) {
        $this->m_sTableName = "\"USERS\"";
        
        $this->m_sPkField = "id";
        
        parent::__construct($iId);
    }
}