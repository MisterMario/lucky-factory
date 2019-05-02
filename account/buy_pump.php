<?php

/*
	Скрипт обработчик. Используется при покупке насосной станции пользователем.
*/


require_once("config.php");
require_once("functions.php");


// Проверяем, что пользователь онлайн, и что обработчик вызван постом
if ($user and isset($_POST["buy_pump"])){
	
	$user_id = $user->getId();
	$category = $_POST["category"];
	$user_money = $user->getMoney();
	
	// Получаем стоимость станции
	$price = QueryDB("SELECT $db_pPrice FROM $db_table_pumps WHERE $db_pCategory='$category'", 1);
	$price = $price[$db_pPrice];
	// Проверка достаточно ли у пользователя денег для покупки
	if ($user_money>=$price){
		// Получаем количство насосов пользователя нужной категории
		$count = QueryDB("SELECT $db_sCount FROM $db_table_store WHERE $db_sUserID='$user_id' AND $db_sCategory='$category' LIMIT 1", 1);
		$count = $count[$db_sCount];
		// Обновляем данные
		$count++;
		QueryDB("UPDATE $db_table_store SET $db_sCount='$count' WHERE $db_sUserID='$user_id' AND $db_sCategory='$category'", 0);
		// Забираем деньги за станцию и обновляем баланс в БД
		$user_money -= $price;
		$user->setMoney($user_money);
		$message = "Приобретена насосная станция ".$category."-й категории, в количестве - 1 шт.";
	} else {
		$message = "Недостаточно средств для приобретения станции!";
	}
}

?>