<?php

/*
	Скрипт для получения контента страницы "account_store"
*/


require_once("config.php");
require_once("functions.php");


$bonus_table = "";


// Проверяем, что пользователь онлайн
if ($user){
	
	// Подгружаем необходимые элементы из БД
	$result_arr = QueryDB("SELECT * FROM $db_table_bonus ORDER by $db_bonusID DESC LIMIT 20", 2);
	// Вставляем элементы в контент
	$bonus_table = "<div id=\"table\">
						<p class=\"table_name\">Последние 20 бонусов</p>
						<table>
							<tr>
								<td>ID</td><td>Пользователь</td><td>Сумма</td><td>Время</td>
							</tr>";
							
	// Проверка того, что количество полученных данных не равно 0
	if (mysql_num_rows($result_arr) != 0){
		while ($row = mysql_fetch_array($result_arr)){ // перебор массива с информацией о насосах
			$bonus_table .="<tr>
								<td>$row[$db_bonusUserID]</td><td>$row[$db_bonusUsername]</td><td>$row[$db_bonusSum]</td><td>".TimestampToStr($row[$db_bonusTime])."</td>
							</tr>";
		}
		
	} else {
		$bonus_table .= "<tr>
							<td colspan=\"4\">Сегодня еще никто не получал ежедневный бонус...</td>
						 </tr>";
	}
	$bonus_table .= "</table>
					 </div>";
	
	include $way_style."account_bonus.html";
	$content .= ob_get_clean();
}

?>