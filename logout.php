<?php

/*
	Скрипт выхода из аккаунта
*/

require_once("config.php");


session_start();

if ($user){
	
	// Удаляем tmp строку из БД
	$id = $_SESSION["user_id"];
	QueryDB("UPDATE $db_table_accounts SET $db_aTmp='',$db_aSession='' WHERE $db_aUserID='$id'", 0);
	// Очищаем и разрушаем сессию
	session_unset();
	session_destroy();
	// Удаляем куки
	setcookie("lucfactory-id", "", time()-3600, "/");
	setcookie("lucfactory-tmp", "", time()-3600, "/");
	// Перезагружаем страницу
	header("Location: http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]);
}

?>