<?php
class Controller_User extends AbstractController {
    const U_ACTION = "usr_action";
    const U_LOGIN = 'usr_login';
    const U_PASS = 'usr_pass';
    const U_VERIF = 'usr_pass_verif';
    const U_WARR = 'usr_warr';
    
    const ACT_IN = "act_login";
    const ACT_REG = "act_regist";
    
    const NO_LOGIN_ERR = "Укажите логин";
    const NO_PASS_ERR = "Укажите пароль";
    const NO_PASSVERIF_ERR = "Пароль не подтвержден";
    const NO_USER_ERR = "Неверный логин или пароль";
    const BAD_LOGIN_ERR = "Логин уже занят";
    
    private $aActionName = array(self::ACT_IN => "Авторизация",
                                  self::ACT_REG => "Регистрация");

    public function __construct() {
        $this->m_sTemplate = Controller_User::PATH_TMPL_DIR . "view_user.tmpl";
            
        $this->parsePost();
        
        $this->assign("act_type", $this->aActionName[$this->m_aPost[self::U_ACTION]]);
        $this->assign("warr", $this->warrningMsg());
        $this->assign("pass_verif", $this->passVerif());
        $this->assign("act_submit", $this->aActionName[$this->m_aPost[self::U_ACTION]]);
        
        $this->setActionFlag();
    }
    
    public function login($param = null) {
        $_SESSION[self::U_ACTION] = self::ACT_IN;
        
        $this->redirect();
    }
    
    public function regist($param = null) {
        $_SESSION[self::U_ACTION] = self::ACT_REG;
        
        $this->redirect();
    }
    
    
    public function logout($param = null) {
        User::getInstance()->logout();
        
        $this->redirect("../gbook/");
    }

    

    public function action($param = null) {
        switch ($this->m_aPost[self::U_ACTION]) {
            case self::ACT_IN:
                $res = $this->authUser();
                break;
            case self::ACT_REG:
                $res = $this->regUser();
                break;
            default:
                $this->redirect();
                return;
        }
        
        if (!$res) {
            $this->redirect("../gbook");
            return;
        }
        
        $_SESSION[self::U_WARR] = $res;
        
        $this->setActionFlag();
        
        $this->redirect();
    }

    // PRIVATE
    private function parsePost() {
        if (isset($_SESSION[self::U_WARR]) && $_SESSION[self::U_WARR]) {
                $this->m_aPost[self::U_WARR] = $_SESSION[self::U_WARR];
                $_SESSION[self::U_WARR] = NULL;
                unset($_SESSION[self::U_WARR]);
        }
        
        /// \@fixme: сделать проверку на значения
        if (isset($_SESSION[self::U_ACTION]) && $_SESSION[self::U_ACTION]) {
                $this->m_aPost[self::U_ACTION] = $_SESSION[self::U_ACTION];
        }
                    
        if (isset($_POST[self::U_LOGIN]) && $_POST[self::U_LOGIN]) {
            $this->m_aPost[self::U_LOGIN] = htmlspecialchars($_POST[self::U_LOGIN]);
        } 

        if (isset($_POST[self::U_PASS]) && $_POST[self::U_PASS]) {
            $this->m_aPost[self::U_PASS] = md5(htmlspecialchars($_POST[self::U_PASS]));
        }
        
        if (isset($_POST[self::U_VERIF]) && $_POST[self::U_VERIF]) {
            $this->m_aPost[self::U_VERIF] = md5(htmlspecialchars($_POST[self::U_VERIF]));
        }
    }        
    
    private function passVerif() {
        if ($this->m_aPost[self::U_ACTION] != self::ACT_REG) {
            return " ";
        }
        
        return file_get_contents(parent::PATH_TMPL_DIR . "view_newuser.tmpl");
    }
    
    
    private function warrningMsg(){
        if (!empty($this->m_aPost[self::U_WARR])) {
            return $this->m_aPost[self::U_WARR];
        }
        
        return " ";
    }
    
    
    private function setActionFlag() {
        $_SESSION[self::U_ACTION] = $this->m_aPost[self::U_ACTION];
    }
    
    
    private function authUser() {
        //login
        if (!isset($this->m_aPost[self::U_LOGIN])) {
            return self::NO_LOGIN_ERR;
        }
    
        //pass
        if (!isset($this->m_aPost[self::U_PASS])) {
            return self::NO_PASS_ERR;
        }
    
        if (!User::getInstance()->auth($this->m_aPost[self::U_LOGIN],
                                       $this->m_aPost[self::U_PASS])) {
            return self::NO_USER_ERR;
        }
        
        return FALSE; //авторизация прошла и нет ошибки
    }
    
    
    private function regUser() {
        //login
        if (!isset($this->m_aPost[self::U_LOGIN])) {
            return self::NO_LOGIN_ERR;
        }
        
        //uniqe
        if (!$this->isLoginUniqe()) {
            return self::BAD_LOGIN_ERR;
        }
    
        //pass
        if (!isset($this->m_aPost[self::U_PASS])) {
            return self::NO_PASS_ERR;
        }
        
        //verif
        if (!(isset($this->m_aPost[self::U_VERIF]) &&
            $this->m_aPost[self::U_VERIF] == $this->m_aPost[self::U_PASS])) {
            return self::NO_PASSVERIF_ERR;
        }
    
        /// \@wtf: это ж надо проверять, так? как это вообще красиво делается?
        if (!$this->addNewUser()) {
            return "Ошибка добавления нового пользователя";
        }
        
        if (!User::getInstance()->auth($this->m_aPost[self::U_LOGIN],
                                       $this->m_aPost[self::U_PASS])) {
            /// \@wtf: теоретически  сюда не должно попасть
            return self::NO_USER_ERR;
        }
        
        return FALSE; ////регистрация прошла и нет ошибки
    }
    
    
    private function isLoginUniqe() {
        $aResult = Model::factory(Model::MODEL_USER)
                ->where(array("user_login" => $this->m_aPost[self::U_LOGIN]))
                ->fetchAll();
        
        return empty($aResult);
    }
            
    
    private function addNewUser() {
        $oUser = Model::factory(Model::MODEL_USER);
        
        $oUser->values(array("user_login" => $this->m_aPost[self::U_LOGIN],
                             "user_pass" => $this->m_aPost[self::U_PASS]));
        
        $oUser->save();
        
        return $oUser->loaded();
    }
}