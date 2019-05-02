<?php

/*
	Скрипт обработчик. Используется при переработке воды в луц.
*/


require_once("config.php");
require_once("functions.php");


// Проверяем, что пользователь онлайн, и что обработчик вызван постом
if ($user and isset($_POST["buy_lottery"])){
	
	$user_id = $user->getId();
	$username = $user->getName();
	
	// Получаем список лотерейных билетов
	$result = QueryDB("SELECT $db_lotteryID FROM $db_table_lottery WHERE $db_lotteryUserID='$user_id' LIMIT 1", 1);
	
	// Проверка того, что пользователь не покупал билет ранее
	if (!$result){
		
		// Проверка того, что у пользователя достаточно денег
		if ($user->getMoney() >= 1000){
			
			$user->setMoney($user->getMoney()-1000);
			QueryDB("INSERT INTO $db_table_lottery($db_lotteryUserID, $db_lotteryUsername) VALUES('$user_id', '$username')", 0);
			$message = "Был приобретен один лотерейный билет!";
			
		} else {
			$message = "У вас недостаточно денег для приобретения билета!";
		}
	} else {
		$message = "Вы уже приобрели один лотерейный билет!";
	}
}

?>