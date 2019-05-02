<?php

/*
	Скрипт авторизации
*/

require_once("config.php");
require_once("functions.php");


$login = $_POST["login_name"];
$password = $_POST["login_password"];

/* Функция для генерации случайной строки */
function TmpGenerate($tmp_length = 32){

	$allchars = "abcdefghijklmnopqrstuvwxyz0123456789";
	$output = "";

	 // Инициализация генератора случайных чисел
	 mt_srand( (double) microtime() * 1000000 );

	 for($i = 0; $i < $tmp_length; $i++){
		 $output .= $allchars{ mt_rand(0, strlen($allchars)-1) };
	 }
	 return $output;
}

/* Функция для сравнения паролей */
function CheckPassword($pass1, $pass2){
	// Сравниваем пароли
	if ($pass1 == $pass2){
		return true;
	} else {
		return false;
	}
}


session_start();

// Проверка того, что пользователь уже не авторизирован
if (!$user){
	if (!empty($login) and !empty($password)){
		// Обработка входных данных
		htmlspecialchars($login, ENT_QUOTES);
		htmlspecialchars($password, ENT_QUOTES);

		// Получаем данные о пользователе
		$result = QueryDB("SELECT $db_aUserID, $db_aPassword FROM $db_table_accounts WHERE $db_aUsername='$login' LIMIT 1", 1);
		// Проверка существования пользователя
		if ($result){
			// Получение хеша от введенного пароля
			$password = GetHash($password);

			// Проверка правильности введенного пароля
			if (CheckPassword($password, $result[$db_aPassword])){
				// Получаем все данные о пользователе
				$user_data = GetAllUserData($result[$db_aUserID]);
				if ($user_data){
					// Создаем представителя класса User
					$user = new User($user_data);
					// Создаем сесию
					$_SESSION["user_id"] = $user_data["user_id"];
					$_SESSION["user_name"] = $user_data["user_name"];
					$_SESSION["user_lvl"] = $user_data["user_lvl"];
					$_SESSION["user_avatar"] = $user_data["user_avatar"];
					$_SESSION["user_money"] = $user_data["user_money"];
					$_SESSION["user_email"] = $user_data["user_email"];
					$_SESSION["user_reg_date"] = $user_data["user_reg_date"];
					// Генерируем tmp-строку и создаем куки
					$id = $user_data["user_id"];
					$tmp = TmpGenerate();
					$session = session_id();
					QueryDB("UPDATE $db_table_accounts SET $db_aTmp='$tmp', $db_aSession='$session' WHERE $db_aUserID='$id'", 0);
					setcookie("lucfactory-id", $id, time()+3600*24*30, "/");
					setcookie("lucfactory-tmp", $tmp, time()+3600*24*30, "/");
				} else {
					$message = "Системная ошибка! Не удается получить данные о пользователе!";
				}
			} else {
				$message = "Ошибка! Неверный пароль!";
			}
		} else {
			$message = "Ошибка! Пользователя с таким именем не существует!";
		}
	} else {
		$message = "Ошибка! Не все поля заполнены!";
	}
}

?>
