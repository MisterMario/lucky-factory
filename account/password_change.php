<?php

/*
	Скрипт обработчик. Используется при смене пароля от аккаунта.
*/


require_once("config.php");
require_once("functions.php");


// Проверяем, что пользователь онлайн, и что обработчик вызван постом
if ($user and isset($_POST["password_change"])){
	
	$user_id = $user->getId();
	$password = addslashes(trim($_POST["password_change"]));
	$password_repeat = addslashes(trim($_POST["password_change_repeat"]));
	
	// Проверка того, что такой пароль уже не используется на аккаунте
	$check_password = QueryDB("SELECT $db_aPassword FROM $db_table_accounts WHERE $db_aUserID='$user_id' LIMIT 1", 1);
	$check_password = $check_password[$db_aPassword];
	
	if (GetHash($password) == $check_password){
		$message = "На вашем аккаунте уже используется такой пароль!";
	} elseif ((strlen($password)<6) and (strlen($password)>32)){
		$message = "Пароль недопустимой длины";
	} elseif($password != $password_repeat){
		$message = "Пароли не совпадают!";
	} elseif (!ValidPassword($password, $password_repeat)){
		$message = "Введен неккоректный пароль!";
	} else {
		
		// Изменяем пароль от аккаунта
		$password = GetHash($password);
		QueryDB("UPDATE $db_table_accounts SET $db_aPassword='$password' WHERE $db_aUserID='$user_id'", 0);
		$message = "Пароль успешно изменен!";
		
	}
	
}

?>