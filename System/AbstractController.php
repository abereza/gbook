<?php
abstract class AbstractController {
    const PATH_TMPL_DIR = "./application/views/";
    
    protected $m_sTemplate;
    
    protected $m_aParams;
    
    protected $m_aGet;

    protected $m_aPost;
    
    abstract public function __construct();
    
    protected function redirect($sLink = null) {
        $page = explode("_", get_class($this));
        header('Location: http://'.$_SERVER['HTTP_HOST']
                .'/'.lcfirst($page[1]) 
                ."/".$sLink, true);
    }
            

    public function assign($sVarName, $sVarValue) {
        $this->m_aParams[$sVarName] = $sVarValue;
    }
    
    public function render() {
        $template = file_get_contents($this->m_sTemplate);
  
        return vsprintf($template, $this->m_aParams);
    }
    
    static public function badPage($nError, $sError) {
         $template= file_get_contents(self::PATH_TMPL_DIR . "view_error.tmpl");
         return sprintf($template, $nError, $sError);
    }
}