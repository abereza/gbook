<?php 
class Controller_Gbook extends AbstractController {
    const SESS_ID = User::SESS_ID;
    const USER_IP = 'REMOTE_ADDR';
    const PAGE_NB = 'gb_page';
    const EDIT_ID = 'gb_editmsg_id';
    const MSG_TEXT = 'gb_msg_text';
    const MSG_IN = 'gb_input';
    
    const MAX_RECORDS_ON_PAGE = 5;
    const NO_EDIT = 0;
    
    const IPV4_PATTERN = '((25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(25[0-5]|2[0-4]\d|[01]?\d\d?)';
    const IPV6_PATTERN = '((^|:)([0-9a-fA-F]{0,4})){1,8}$';
    
    const ADD_MSG = 'Оставьте ваш отзыв';
    const EDIT_MSG = 'Редактирование сообщения';
    const NO_MSG_TEXT = 'В гостевой книге пока нет записей. Будьте первыми!';


    public function __construct() {
        $this->m_sTemplate = parent::PATH_TMPL_DIR . "view_gbook.tmpl";
        
        $this->parsePost();
        
        $this->assign("user_name", User::getInstance()->name());
        $this->assign("user_action", $this->actionLineView());
        $this->assign("msg_block", $this->messagesView());
        $this->assign("navi_block", $this->inputNaviView());
        if ($this->m_aPost[self::EDIT_ID] == self::NO_EDIT) {
            $this->assign("msg_input", $this->inputMsgView(" "));
        }
        else {
            $this->assign("msg_input", " ");
        }
    }
   
    // NAVI
    public function rwndFirst($param = null) {
        $_SESSION[self::PAGE_NB] = 1;
        
        $this->redirect();
    }
    
    public function rwndBack($param = null) {
        if (!($this->m_aPost[self::PAGE_NB]--)) {
            $_SESSION[self::PAGE_NB] = 1;
        }
        else {
            $_SESSION[self::PAGE_NB] = $this->m_aPost[self::PAGE_NB];
        }
            
        $this->redirect();
    }
    
    public function rwndForward($param = null) {
        $iPageCnt = $this->getPageCount();
        if (($this->m_aPost[self::PAGE_NB]++) > $iPageCnt) {
            $_SESSION[self::PAGE_NB] = $iPageCnt;
        }
        else {
            $_SESSION[self::PAGE_NB] = $this->m_aPost[self::PAGE_NB];
        }
            
        $this->redirect();
    }
    
    public function rwndLast($param = null) {
        $_SESSION[self::PAGE_NB] = $this->getPageCount();
        
        $this->redirect();
    }
    
    
    public function inputmsg($param = null) {
        if (isset($this->m_aPost[self::MSG_TEXT])) {    
            $oMsg = Model::factory(Model::MODEL_MESG);

            $oMsg->values(array("user_id" => User::getInstance()->id(),
                                "user_ip" => $this->m_aPost[self::USER_IP],
                                "date_r" => date(DATE_RFC822),
                                "content" => $this->m_aPost[self::MSG_TEXT]));

            $bUpdate = false;
            
            if ($this->m_aPost[self::EDIT_ID] != self::NO_EDIT) {
                $oMsg->values(array("id" => $this->m_aPost[self::EDIT_ID]));
                $bUpdate = true;
            }

            $oMsg->save($bUpdate);
        }
        
        if ($this->m_aPost[self::EDIT_ID] != self::NO_EDIT) {
            $this->redirect(); //с сохранением текущей страницы
        }
        else {
            $this->rwndFirst();
        }
    }
    
    
    public function editmsg($param = null) {
        if (!is_null($param)) {
            $_SESSION[self::EDIT_ID] = $param;
        }
        
        $this->redirect();
    }

    // PRIVATE
    private function parsePost() {
        if (isset($_SESSION[self::SESS_ID]) && $_SESSION[self::SESS_ID] &&
                    is_numeric($_SESSION[self::SESS_ID])) {
                $this->m_aPost[self::SESS_ID] = $_SESSION[self::SESS_ID];
        }
            
        if (isset($_SESSION[self::PAGE_NB]) && $_SESSION[self::PAGE_NB] &&
                    is_numeric($_SESSION[self::PAGE_NB])) {
                $this->m_aPost[self::PAGE_NB] = $_SESSION[self::PAGE_NB];
                $this->checkPageNumber();
        } 
        else {
            $this->m_aPost[self::PAGE_NB] = 1;
        }
        
        if (isset($_SESSION[self::EDIT_ID]) && $_SESSION[self::EDIT_ID] &&
                  is_numeric($_SESSION[self::EDIT_ID])) {
            $this->m_aPost[self::EDIT_ID] = $_SESSION[self::EDIT_ID];
        }
        else {
            $this->m_aPost[self::EDIT_ID] = self::NO_EDIT;
        }
        
        if (isset($_POST[self::MSG_TEXT]) && $_POST[self::MSG_TEXT]) {
            $this->m_aPost[self::MSG_TEXT] = htmlspecialchars($_POST[self::MSG_TEXT]);
        }
        
        if (isset($_POST[self::MSG_IN])) {
            $this->dropEditID();
        }
        
        if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR']) {
            /// \@wtf : чет не пашет зараза
            // (preg_match(self::IPV4_PATTERN, $_SERVER['REMOTE_ADDR']) ||
            //  preg_match(self::IPV6_PATTERN, $_SERVER['REMOTE_ADDR']))) {
                $this->m_aPost[self::USER_IP] = $_SERVER['REMOTE_ADDR'];        
        }
        else {
            $this->m_aPost[self::USER_IP] = "undef";
        }
    }        
    
    
    private function actionLineView() {
        if (User::getInstance()->logged()) {
            return file_get_contents(parent::PATH_TMPL_DIR . "view_useraction.tmpl");
        }
        
        return file_get_contents(parent::PATH_TMPL_DIR . "view_guestaction.tmpl");
    }
    
    
    private function messagesView() {
        if (!$this->getRowCount()) {
            return self::NO_MSG_TEXT;
        }
             
        $tmpl = file_get_contents(parent::PATH_TMPL_DIR . "view_mesg.tmpl");
        
        $aMesgs = Model::factory(Model::MODEL_MESG)
                ->order(array("id" => "DESC"))
                ->limit(array(self::MAX_RECORDS_ON_PAGE => $this->getPageOffset()))
                ->fetchAll();

        $str = " ";

        foreach ($aMesgs as $mesg) {  
            if ($mesg->id == $this->m_aPost[self::EDIT_ID]) {
                $str .= $this->inputMsgView($mesg->content);
                continue;
            }
            
            $str .= sprintf($tmpl, 
                    $this->getUserLoginByID($mesg->user_id),
                    (string)$mesg->date_r,
                    $mesg->user_ip,
                    $this->editView($mesg->user_id, $mesg->id),
                    $mesg->content);
        } 
        
        return $str;
    }
    
    
    private function inputMsgView($sText) {
        if ($this->m_aPost[self::EDIT_ID] != self::NO_EDIT) {
            $aParams = array(self::EDIT_MSG, $sText);
        }
        else {
            $aParams = array(self::ADD_MSG, " ");
        }
        
        $template = file_get_contents(parent::PATH_TMPL_DIR . "view_inputmsg.tmpl");
        
        return vsprintf($template, $aParams);
    }
    
    
    private function inputNaviView() {
        $pageCount = $this->getPageCount();
        
        if ($pageCount == 1) {
            return ""; 
        }
    
        $tmpl = file_get_contents(parent::PATH_TMPL_DIR . "view_navi.tmpl");

        if ($this->m_aPost[self::PAGE_NB] == 1) {
            return sprintf($tmpl, "hidden", "hidden", "", "");
        }
   
        if ($this->m_aPost[self::PAGE_NB] >= $pageCount) {
            return sprintf($tmpl, "", "", "hidden", "hidden");
        }
        
        return sprintf($tmpl, "", "", "", "");
    }
    
    
    private function editView($iID, $iMsgID) {
        if (User::getInstance()->id() == User::GUEST_SESS_ID ||
            $iID != User::getInstance()->id()) {
            return " ";
        }
        
        $template= file_get_contents(self::PATH_TMPL_DIR . "view_useredit.tmpl");
        return sprintf($template, $iMsgID);
    }

    private function checkPageNumber() {
        $pageCount = $this->getPageCount();
    
        if ($this->m_aPost[self::PAGE_NB] > $pageCount) {
            $this->m_aPost[self::PAGE_NB] = $pageCount;
        } 
        elseif ($this->m_aPost[self::PAGE_NB] < 1) {
            $this->m_aPost[self::PAGE_NB] = 1;
        }
    }
           

    private function getPageOffset()
    {
        return (($this->m_aPost[self::PAGE_NB] - 1) * self::MAX_RECORDS_ON_PAGE);
    }  
    
    
    private function getPageCount()
    {
        $rowCount = $this->getRowCount();
        
        return ($rowCount % self::MAX_RECORDS_ON_PAGE)?
                 ((int)($rowCount/self::MAX_RECORDS_ON_PAGE) + 1):
                 ((int)($rowCount/self::MAX_RECORDS_ON_PAGE));
    }
    
    
    private function getRowCount()
    {
        return Model::factory(Model::MODEL_MESG)->rowCount();
    }
    
    
    private function getUserLoginByID($iID) {
        if ($iID == User::GUEST_SESS_ID) {
            return User::ANONIM_LOGIN;
       }
       return Model::factory(Model::MODEL_USER, $iID)->user_login;
    }
    
    private function dropEditID() {
        $_SESSION[self::EDIT_ID] = NULL;
        unset($_SESSION[self::EDIT_ID]);
    }
}