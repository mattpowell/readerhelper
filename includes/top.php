<?php
session_start();

$APPLICATION_ENVIRONMENT=$_SERVER["APPLICATION_ENVIRONMENT"];

ob_start();
//so config data doesn't leak if there's ever a misconfiguration 
require_once($APPLICATION_ENVIRONMENT=="dev"?"dev-Config.class.php":($APPLICATION_ENVIRONMENT=="qa"?"qa-Config.class.php":"prod-Config.class.php"));
require_once("Access.class.php");
ob_end_flush();


function encrypt($text){
    return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, Config::USERID_SALT, $text, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))));
}

function decrypt($text){
    return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, Config::USERID_SALT, base64_decode($text), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));
}

?>