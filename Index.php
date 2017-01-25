<?php
error_reporting(E_ALL);
require_once 'application/config/config.php';

spl_autoload_register( function($className) {
    $dirs = array(
            'System/',
            'System/Db/',
            'application/'
        );
    
    $className = str_replace('_', '/', $className);
    
    foreach ($dirs as $dir) {
        if (file_exists($dir . '/' . $className . '.php')) {
            require_once $dir . '/' . $className . '.php';
            return true;
        }
    }

    throw new Exception("Class $className could not be loaded");
});
  
session_start();

if (!Db::getInstance()->create($aDbConfig)) {
    echo AbstractController::badPage(400, "Нет подключения к БД");
    return;
}


if (!(isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'])) {
    /// \@wtf: стремное место, не думаю, что 404 лучший вариант
    echo AbstractController::badPage(404, "Страница не найдена");
    return;
}

$aRequest = explode("/", $_SERVER['REQUEST_URI']);
/// \@wtf: массив грязный, есть пустые элементы...надо б их удалить

try {
    if (!class_exists($className = "Controller_" . ucfirst($aRequest[1]))) {
        echo AbstractController::badPage(404, "Страница не найдена");
        return;
    }

    $a = new $className();
    
    /// \@wtf: если в адр.строке http://project:81/gbook/ то  работают относительные ссылки аля ./input 
    /// а если  http://project:81/gbook  работают такие gbook/input.  Как исправить?
    
    if (isset($aRequest[2]) && $aRequest[2]) {
        $methodName = $aRequest[2];
        $methodParam = (isset($aRequest[3]))?(htmlspecialchars($aRequest[3])):(null);
            
        if (!method_exists($a, $methodName)) {
            throw new Exception('Failed in index.php at line '.__LINE__ . ' method is '. $methodName);
        }
        $a->$methodName($methodParam);
    } 
    else {
        echo $a->render();
    }
}
catch (Exception $e) {
    echo "Expetion: " . $e->getMessage();
    echo AbstractController::badPage(404, "Страница не найдена");
} 

