<?php
class User {
    const ANONIM_LOGIN = 'Гость';
    const SESS_ID = 'gb_session_id';
    const GUEST_SESS_ID = -1;
    
    private static $m_pObj = null;
    
    private $m_sUserName;

    private $m_bAuthFlag;
    
    private $m_iUserID;

    private function __construct() {
    }
    
    private function __clone() {
    }
        
    private function __wakeup() {
    }
    
    private function verifiSession() {
        if (!(isset($_SESSION[self::SESS_ID]) && 
            is_numeric($_SESSION[self::SESS_ID]) &&
            ($_SESSION[self::SESS_ID] != self::GUEST_SESS_ID))) {
            return FALSE;
        }
        
        $oUser = Model::factory(Model_User::MODEL_USER, $_SESSION[self::SESS_ID]);
        
        if ($oUser->loaded()) {
            $this->setUser($oUser->user_login, $_SESSION[self::SESS_ID]);
            return TRUE;
        }
        
        return FALSE;
    }
    
    
    private function setUser($sLogin, $iSessID) {
        $this->m_sUserName = $sLogin;
        $this->m_bAuthFlag = true;
        $this->m_iUserID = $iSessID; 
        
        $this->setSession($iSessID); 
    }

    
    private function setSession($iSessID) {
        $_SESSION[self::SESS_ID] = $iSessID;
    }

    
    private function dropSession() {
        $_SESSION[self::SESS_ID] = NULL;
        unset($_SESSION[self::SESS_ID]);
    }

    
    public static function getInstance() {
        if (is_null(self::$m_pObj)) {
            self::$m_pObj = new self;
        }
        
        if (!self::$m_pObj->verifiSession()) {
            self::$m_pObj->logout();
        }
        
        return self::$m_pObj;
    }
   

    public function auth($sLogin, $sPass) {
        $result = Model::factory(Model::MODEL_USER)->where(array('user_login' => $sLogin,
                                                                 'user_pass' => $sPass))
                    ->fetchAll();
        
        if (empty($result)) {
            return FALSE;
        }
        
        $this->setUser($sLogin, $result[0]->id);

        return TRUE;        
    }
    
    public function logged() {
        return $this->m_bAuthFlag;
    }
    
    public function name() {
        return $this->m_sUserName;
    }
    
    
    public function id() {
        return $this->m_iUserID;
    }

        public function logout() {
        $this->m_sUserName = self::ANONIM_LOGIN;
        $this->m_bAuthFlag = FALSE;
        $this->m_iSess = self::GUEST_SESS_ID;
        $this->m_iUserID = self::GUEST_SESS_ID;
        
        $this->dropSession();
    }
}