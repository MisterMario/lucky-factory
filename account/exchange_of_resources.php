<?php

/*
	Скрипт обработчик. Используется при переработке воды в луц.
*/


require_once("config.php");
require_once("functions.php");


// Проверяем, что пользователь онлайн, и что обработчик вызван постом
if ($user and isset($_POST["exchange_of_resources"])){
	
	$user_id = $user->getId();
	$counter = 0; // Количество полученного в результате перарботки ресурса
	
	// Получаем данные из БД
	$result = QueryDB("SELECT $db_sCategory, $db_sTank, $db_sResource FROM $db_table_store WHERE $db_sUserID='$user_id'", 2);
	
	// Перебираем все станции
	while ($row = mysql_fetch_array($result)){
		// Проверяем есть ли в данной станции вода
		if ($row[$db_sTank] != 0){
			// Прибавляем к существующему переработанный луц
			$sum = $row[$db_sResource]+$row[$db_sTank];
			$counter += $row[$db_sTank];
			// Обновляем значения в БД
			QueryDB("UPDATE $db_table_store SET $db_sTank='0', $db_sResource='$sum' WHERE $db_sUserID='$user_id' AND $db_sCategory='$row[$db_sCategory]'", 0);
			// Записываем результаты переработки, для их последующего вывода
			$_POST["ex_resource_out"][ $row[$db_sCategory] ] = $row[$db_sTank];
		}
	}
	// Сообщение о результатах переработки
	if ($counter != 0){
		$message = "Переработка воды в луц успешно завершена! Получено ".$counter." грамм!";
	} else {
		$message = "Ошибка! У вас нет воды в хранилище!";
	}
}

?>