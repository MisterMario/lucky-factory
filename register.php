<?php

/*
	Скрипт регистрации
*/


require_once("config.php");
require_once("functions.php");


$login = $_POST["registration_name"];
$password = $_POST["registration_password"];
$password_repeat = $_POST["registration_password_repeat"];
$email = $_POST["registration_email"];
$rules = $_POST["registration_check_rules"];

// Подготовка входных данных
$login = addslashes(trim($login));
$password = addslashes(trim($password));
$password_repeat = addslashes(trim($password_repeat));
$email = addslashes(trim($email));


// Проверка того, что пользователь не авторизирован
if (!$user){
	
	// Проверка на то, что пользователь с таким именем уже не зарегистрирован
	$check_login = QueryDB("SELECT $db_aUserID FROM $db_table_accounts WHERE $db_aUsername='$login' LIMIT 1", 1);
	$check_email = QueryDB("SELECT $db_aUserID FROM $db_table_accounts WHERE $db_aEmail='$email' LIMIT 1", 1);
	
	if ((empty($login)) or (empty($password)) or (empty($password_repeat)) or (empty($email))){
		$message = "Не все поля заполнены!";
	} elseif (!$rules){
		$message = "Для начала вам необходимо подтвердить согласие с правилами проекта!";
	} elseif ((strlen($login)<6) or (strlen($login)>32)){
		$message = "Логин недопустимой длины!";
	} elseif ((strlen($password)<6) and (strlen($password)>32)){
		$message = "Пароль недопустимой длины";
	} elseif (!ValidLogin($login)){
		$message = "Логин содержит недопустимые символы!";
	} elseif (!ValidPassword($password, $password_repeat)){
		$message = "Пароль содержит недопустимые символы";
	} elseif($password != $password_repeat){
		$message = "Пароли не совпадают!";
	} elseif (!ValidEmail($email)){
		$message = "Введен некорректный e-mail!";
	} elseif ($check_login) {
		$message = "Пользователь с таким именем уже зарегистрирован!";
	} elseif ($check_email){
		$message = "Пользователь с таким e-mail-ом уже зарегистрирован!";
	} else {
		
		// Получение двойного md5 хеша пароля
		$password = GetHash($password);
		
		// Добавляем данные о пользователе (регистрационные и баланс)
		QueryDB("INSERT INTO $db_table_accounts($db_aUsername, $db_aPassword, $db_aEmail) 
				 VALUES('$login', '$password', '$email')", 0);
		QueryDB("INSERT INTO $db_table_balance($db_bMoney) VALUES('20000')", 0);
		
		/* Получаем категории всех существующих насосных станций и записываем их пользователю
		   в хранилище. Все происходит именно таким образом для того, что при добавлении новых станций
		   не пришлось бы дописывать срипт регистрации. */
		
		// Для начала получаем id нового пользователя
		$result = QueryDB("SELECT $db_aUserID FROM $db_table_accounts WHERE $db_aUsername='$login' LIMIT 1", 1);
		$user_id = $result[$db_aUserID];
		
		// Получаем категории всех существующих станций, для добавления их в хранилище
		$result = QueryDB("SELECT $db_pCategory FROM $db_table_pumps ORDER by $db_sCategory DESC", 2);
		while ($row = mysql_fetch_array($result)){
			QueryDB("INSERT INTO $db_table_store($db_sUserID, $db_sCategory) VALUES('$user_id','$row[$db_pCategory]')", 0);
		}
		$message = "Регистрация успешно завершена!";
		
	}
}


?>