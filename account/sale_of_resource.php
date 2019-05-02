<?php

/*
	Скрипт обработчик. Используется при продаже ресурсов (обмене луца на чатлы).
*/


require_once("config.php");
require_once("functions.php");


// Проверяем, что пользователь онлайн, и что обработчик вызван постом
if ($user and isset($_POST["sale_of_resource"])){
	
	$user_id = $user->getId();
	$counter = 0; // Количество чатлов, полученное в результате обмена
	$sum = $user->getMoney();
	
	// Получаем данные из БД
	$result = QueryDB("SELECT $db_sCategory, $db_sResource FROM $db_table_store WHERE $db_sUserID='$user_id'", 2);
	
	// Перебираем все станции
	while ($row = mysql_fetch_array($result)){
		// Проверяем есть ли в данной станции луц
		if ($row[$db_sResource] != 0){
			// Прибавляем к существующему переработанный луц
			$sum += ($row[$db_sResource]/100);
			$counter += ($row[$db_sResource]/100);
			// Обновляем значения в БД
			QueryDB("UPDATE $db_table_store SET $db_sResource='0' WHERE $db_sUserID='$user_id' AND $db_sCategory='$row[$db_sCategory]'", 0);
		}
	}
	// Сообщение о результатах переработки
	if ($counter != 0){
		// Изменяем значение пользовательского баланса
		$user->setMoney($sum);
		// Вывод сообщения о результате
		$message = "Продажа луца успешно завершена! Получено ".$counter." чатлов!";
	} else {
		$message = "Ошибка! У вас нет луца в хранилище!";
	}
}

?>