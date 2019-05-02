<?php

/*
	Скрипт для получения контента страницы "account_lottery"
*/


require_once("config.php");
require_once("functions.php");


$lottery_table = "";


// Проверяем, что пользователь онлайн
if ($user){
	
	$user_id = $user->getId();
	
	// Подгружаем пользователей купивших лотерейные билеты
	$res = QueryDB("SELECT * FROM $db_table_lottery", 2);
	
	// Формирование таблицы
	$lottery_table = "<div id=\"table\">
						<p class=\"table_name\">Пользователи купившие билеты</p>
							<table>
								<tr><td class=\"bilet_num\">Номер билета</td><td>Пользователь</td><td>Дата</td><td>Состояние</td></tr>";
	
	// Проверка того, что кто-то купил билеты
	if (mysql_num_rows($res) != 0){
		// Получаем информацию о билетах в таблицу
		while ($row = mysql_fetch_array($res)){
			$lottery_table .= "<tr><td>$row[$db_lotteryID]</td><td>$row[$db_lotteryUsername]</td>
							   <td>".TimestampToDate($row[$db_lotteryDate])."</td><td>".CheckLotteryStatus($row[$db_lotteryStatus])."</td></tr>";
		}
	} else {
		$lottery_table .= "<tr><td colspan=\"4\">Нет записей</td></tr>";
	}
	
	// Конец формирования таблицы
	$lottery_table .= "</table>\n</div>\n";
	
	
	include $way_style."account_lottery.html";
	$content .= ob_get_clean();
}

?>