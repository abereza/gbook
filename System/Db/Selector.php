<?php

class Selector {
    private $m_data;
    
    public function __construct($res) {
        $this->m_data = $res;
    }
    
    public function count() {
        return pg_num_rows($this->m_data);
    }
    
    public function fetchAll() {
        return pg_fetch_all($this->m_data);
    }
    
    public function fetchRow($nRow) {
        return pg_fetch_array($this->m_data, $nRow, PGSQL_ASSOC);
    }
    
    public function fetchOne($nRow, $nColumn) {
        return pg_fetch_result($this->m_data, $nRow, $nColumn);
    }
    
    public function isEmpty() {
        return empty($this->m_data);
    }
}
