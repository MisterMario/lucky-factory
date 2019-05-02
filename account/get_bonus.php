<?php

/*
	Скрипт обработчик. Используется при получении ежедневного бонуса.
*/


require_once("config.php");
require_once("functions.php");


// Проверяем, что пользователь онлайн, и что обработчик вызван постом
if ($user and isset($_POST["get_bonus"])){
	
	$user_id = $user->getId();
	$username = $user->getName();
	
	// Получаем данные из БД
	$result = QueryDB("SELECT $db_bonusSum FROM $db_table_bonus WHERE $db_bonusUserID='$user_id' LIMIT 1", 1);
	if (!$result){
		// Генерируем бонус
		mt_srand( (double) microtime() * 1000000 );
		$sum = mt_rand(10, 100);
		$user->setMoney($user->getMoney()+$sum);
		
		// Выдаем пользователю бонус
		QueryDB("INSERT INTO $db_table_bonus($db_bonusUserID, $db_bonusUsername, $db_bonusSum)
				 VALUES('$user_id', '$username', '$sum')", 0);
		$message = "Получен бонус в размере ".$sum." чатлов!";
	} else {
		$message = "Сегодня вы уже получили ежедневный бонус!";
	}
}

?>