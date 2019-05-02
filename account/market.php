<?php

/*
	Скрипт для получения контента страницы "account_store"
*/


require_once("config.php");
require_once("functions.php");


$market_table = "";


// Проверяем, что пользователь онлайн
if ($user){
	
	$user_id = $user->getId();
	// Подгружаем необходимые элементы из БД
	$info_arr = QueryDB("SELECT $db_sCategory, $db_sResource FROM $db_table_store WHERE $db_sUserID='$user_id' ORDER by $db_sCategory DESC ", 2);

	// Вставляем элементы в контент
	$market_table = "<div id=\"market_table\">
						<table>
							<tr>
								<td>Тип насоса</td><td>У вас в наличии</td><td>На сумму</td>
							</tr>";
	while ($row = mysql_fetch_array($info_arr)){ // перебор массива с информацией о насосах
		$market_table .="<tr>
							<td>$row[$db_sCategory]-я категория</td><td>$row[$db_sResource] луца</td><td>".($row[$db_sResource]/100)." чатлов</td>
						</tr>";
	}
	$market_table .= "</table>
					  </div>";
					  
	include $way_style."account_market.html";
	$content .= ob_get_clean();
}

?>