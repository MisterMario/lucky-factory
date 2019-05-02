<?php

/* 
	Системный скрипт движка. Содержит класс User и его методы.
*/

require_once("config.php");
require_once("functions.php");


Class User{
	
	private $id; // id пользователя
	private $name; // псевдоним пользователя
	private $lvl; // уровень прав пользователя
	private $avatar; // аватарка пользователя
	private $money; // баланс пользователя
	private $email; // e-mail пользователя
	private $reg_date; // дата регистрации пользователя
	
	/* Конструктор класса User 
	   Используется именно "метод", так как выборка данных может производиться по различным критериям.
	   Критерий как я полагаю будет выбираться в зависимости от ситуациии (условий). */
	function __construct($input){
		
		$this->id = $input["user_id"];
		$this->name = $input["user_name"];
		$this->lvl = $input["user_lvl"];
		$this->avatar = $input["user_avatar"];
		$this->money = $input["user_money"];
		$this->email = $input["user_email"];
		$this->reg_date = $input["user_reg_date"];
		
	}
	
	/* Методы для обращения к свойствам класса.
	   Написаны для удобства, так как имена у свойств могут измениться и в таком случае придется переписать часть кода */
	function getId(){
		return $this->id;
	}
	
	function getName(){
		return $this->name;
	}
	
	function getLvl(){
		return $this->lvl;
	}
	
	function getAvatar(){
		return $this->avatar;
	}
	
	function getMoney(){
		return $this->money;
	}
	
	function getEmail(){
		return $this->email;
	}
	
	function getReg_date(){
		return $this->reg_date;
	}
	
	function setAvatar($avatar){
		
		$this->avatar = $avatar;
		$_SESSION["user_avatar"] = $avatar;
	}
	
	function setMoney($money){
		global $db_table_balance, $db_bUserID, $db_bMoney;
		
		$this->money = $money;
		$_SESSION["user_money"] = $money;
		QueryDB("UPDATE $db_table_balance SET $db_bMoney='$money' WHERE $db_bUserID='$this->id'", 0);
	}
	
	function setEmail($email){
		global $db_table_accounts, $db_aUserID, $db_aEmail;
		
		$this->email = $email;
		$_SESSION["user_email"] = $email;
		QueryDB("UPDATE $db_table_accounts SET $db_aEmail='$email' WHERE $db_aUserID='$this->id'", 0);
	}
}


session_start();

// Проверяем существование сессии
if ((isset($_SESSION["user_id"])) and (isset($_SESSION["user_name"]))){
	
	// Сессия существует, можно создавать пользователя
	$input = array("user_id" => $_SESSION["user_id"],
				   "user_name" => $_SESSION["user_name"],
				   "user_lvl" => $_SESSION["user_lvl"],
				   "user_avatar" => $_SESSION["user_avatar"],
				   "user_money" => $_SESSION["user_money"],
				   "user_email"=> $_SESSION["user_email"],
				   "user_reg_date"=> $_SESSION["user_reg_date"]);
	
	$user = new User($input);
	
} elseif ((isset($_COOKIE["lucfactory-id"])) and (isset($_COOKIE["lucfactory-tmp"]))){
	// Получаем данные COOKIE
	$id = $_COOKIE["lucfactory-id"];
	$tmp = $_COOKIE["lucfactory-tmp"];
	// Проверка валидности данных из кук
	$result = QueryDB("SELECT $db_aUserID FROM $db_table_accounts WHERE $db_aUserID='$id' AND $db_aTmp='$tmp' LIMIT 1", 1);
	if($result){
		$user_data = GetAllUserData($id);
		$_SESSION["user_id"] = $user_data["user_id"];
		$_SESSION["user_name"] = $user_data["user_name"];
		$_SESSION["user_lvl"] = $user_data["user_lvl"];
		$_SESSION["user_avatar"] = $user_data["user_avatar"];
		$_SESSION["user_money"] = $user_data["user_money"];
		$_SESSION["user_email"] = $user_data["user_email"];
		$_SESSION["user_reg_date"] = $user_data["user_reg_date"];
		
		// Запись сессии в БД
		$user_id = $user_data["user_id"];
		$session = session_id();
		QueryDB("UPDATE $db_table_accounts SET $db_aSession='$session' WHERE $db_aUserID='$user_id'", 0);
		
		// Создаем объект пользователя
		$user = new User($user_data);
	} else { // В случае, если данные из кукисов невалидные
		// Удаляем куки
		setcookie("lucfactory-id", "", time()-3600, "/");
		setcookie("lucfactory-tmp", "", time()-3600, "/");
	}
}

?>