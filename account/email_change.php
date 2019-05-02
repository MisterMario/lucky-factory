<?php

/*
	Скрипт обработчик. Используется при смене e-mail.
*/


require_once("config.php");
require_once("functions.php");


// Проверяем, что пользователь онлайн, и что обработчик вызван постом
if ($user and isset($_POST["email_change"])){
	
	$email = addslashes(trim($_POST["email_change"]));
	
	// Проверка того, что e-mail не занят другим аккаунтом
	$check_email = QueryDB("SELECT $db_aUserID FROM $db_table_accounts WHERE $db_aEmail='$email' LIMIT 1", 1);
	
	if ($email == $user->getEmail()){
		$message = "Введенный e-mail уже используется на вашем аккаунте!";
	} elseif (!ValidEmail($email)){
		$message = "Введен некорректный e-mail!";
	} elseif ($check_email){
		$message = "Пользователь с таким e-mail уже существует!";
	} else {
		
		$user->setEmail($email);
		$message = "Ваш e-mail изменен на ".$email;
		
	}
	
}

?>